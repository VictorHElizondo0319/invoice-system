import { useState, useEffect } from 'react'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { useNavigate, Link } from 'react-router-dom'
import { createInvoice } from '../api/api'
import { useAuth } from '../context/AuthContext'
import CustomerSelect from '../components/CustomerSelect'

export default function CreateInvoice() {
  const navigate = useNavigate()
  const queryClient = useQueryClient()
  const { user } = useAuth()
  useEffect(() => {
    if (!user) return
    if (user.role !== 'admin') {
      alert('You are not authorized to access this page.')
      navigate('/invoices')
    }
  }, [user, navigate])

  const [selectedCustomer, setSelectedCustomer] = useState(null)
  const [dueDate, setDueDate] = useState('')
  const [items, setItems] = useState([
    { description: '', qty: 1, price: '' }
  ])

  const createMutation = useMutation({
    mutationFn: createInvoice,
    onSuccess: (data) => {
      queryClient.invalidateQueries(['invoices'])
      queryClient.invalidateQueries(['summary'])
      navigate(`/invoices/${data.invoice.id}`)
    },
    onError: (error) => {
      alert(`Failed to create invoice: ${error.response?.data?.message || error.message}`)
    },
  })

  const addItem = () => {
    setItems([...items, { description: '', qty: 1, price: '' }])
  }

  const removeItem = (index) => {
    if (items.length > 1) {
      setItems(items.filter((_, i) => i !== index))
    }
  }

  const updateItem = (index, field, value) => {
    const newItems = [...items]
    newItems[index][field] = value
    setItems(newItems)
  }

  const calculateTotal = () => {
    return items.reduce((total, item) => {
      const qty = parseInt(item.qty) || 0
      const price = parseFloat(item.price) || 0
      return total + (qty * price)
    }, 0)
  }

  const handleSubmit = (e) => {
    e.preventDefault()

    // Validation
    if (!selectedCustomer || !selectedCustomer.name) {
      alert('Please select or create a customer')
      return
    }

    if (!dueDate) {
      alert('Please select due date')
      return
    }

    const validItems = items.filter(item => 
      item.description.trim() && item.qty > 0 && item.price > 0
    )

    if (validItems.length === 0) {
      alert('Please add at least one valid item')
      return
    }

    const invoiceData = {
      customer_id: selectedCustomer.id,
      due_date: dueDate,
      items: validItems.map(item => ({
        description: item.description,
        qty: parseInt(item.qty),
        price: parseFloat(item.price)
      }))
    }

    createMutation.mutate(invoiceData)
  }

  const formatAmount = (amount) => {
    return `$${amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
  }

  return (
    <div className="px-4 sm:px-0">
      <div className="mb-4">
        <Link to="/invoices" className="text-indigo-600 hover:text-indigo-800 text-sm">
          ← Back to Invoices
        </Link>
      </div>

      <div className="bg-white shadow rounded-lg">
        <div className="px-6 py-4 border-b border-gray-200">
          <h1 className="text-2xl font-bold text-gray-900">Create New Invoice</h1>
          <p className="mt-1 text-sm text-gray-500">Fill in the details to create a new invoice</p>
        </div>

        <form onSubmit={handleSubmit} className="px-6 py-4 space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label htmlFor="customerName" className="block text-sm font-medium text-gray-700 mb-1">
                Customer *
              </label>
              <CustomerSelect
                value={selectedCustomer}
                onChange={(cust) => setSelectedCustomer(cust)}
                placeholder="Search or create customer"
              />
            </div>

            <div>
              <label htmlFor="dueDate" className="block text-sm font-medium text-gray-700 mb-1">
                Due Date *
              </label>
              <input
                type="date"
                id="dueDate"
                value={dueDate}
                onChange={(e) => setDueDate(e.target.value)}
                min={new Date().toISOString().split('T')[0]}
                className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border"
                required
              />
            </div>
          </div>

          <div>
            <div className="flex justify-between items-center mb-3">
              <h3 className="text-lg font-semibold text-gray-900">Line Items *</h3>
              <button
                type="button"
                onClick={addItem}
                className="px-3 py-1 text-sm bg-indigo-100 text-indigo-700 rounded-md hover:bg-indigo-200"
              >
                + Add Item
              </button>
            </div>

            <div className="space-y-4">
              {items.map((item, index) => (
                <div key={index} className="flex gap-3 items-start bg-gray-50 p-4 rounded-lg">
                  <div className="flex-1">
                    <label className="block text-xs font-medium text-gray-700 mb-1">
                      Description
                    </label>
                    <input
                      type="text"
                      value={item.description}
                      onChange={(e) => updateItem(index, 'description', e.target.value)}
                      className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2 border"
                      placeholder="Service or product description"
                      required
                    />
                  </div>

                  <div className="w-24">
                    <label className="block text-xs font-medium text-gray-700 mb-1">
                      Quantity
                    </label>
                    <input
                      type="number"
                      value={item.qty}
                      onChange={(e) => updateItem(index, 'qty', e.target.value)}
                      min="1"
                      className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2 border"
                      required
                    />
                  </div>

                  <div className="w-32">
                    <label className="block text-xs font-medium text-gray-700 mb-1">
                      Price ($)
                    </label>
                    <input
                      type="number"
                      value={item.price}
                      onChange={(e) => updateItem(index, 'price', e.target.value)}
                      step="0.01"
                      min="0.01"
                      className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2 border"
                      placeholder="0.00"
                      required
                    />
                  </div>

                  <div className="w-32">
                    <label className="block text-xs font-medium text-gray-700 mb-1">
                      Subtotal
                    </label>
                    <div className="text-sm font-medium text-gray-900 px-3 py-2 bg-white rounded-md border border-gray-300">
                      {formatAmount((item.qty || 0) * (item.price || 0))}
                    </div>
                  </div>

                  {items.length > 1 && (
                    <button
                      type="button"
                      onClick={() => removeItem(index)}
                      className="mt-6 p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded"
                      title="Remove item"
                    >
                      ✕
                    </button>
                  )}
                </div>
              ))}
            </div>
          </div>

          <div className="border-t pt-4">
            <div className="flex justify-end">
              <div className="w-64">
                <div className="flex justify-between items-center text-lg font-semibold">
                  <span className="text-gray-900">Total:</span>
                  <span className="text-indigo-600">{formatAmount(calculateTotal())}</span>
                </div>
              </div>
            </div>
          </div>

          <div className="flex gap-3 pt-4 border-t">
            <button
              type="submit"
              disabled={createMutation.isPending}
              className="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 font-medium"
            >
              {createMutation.isPending ? 'Creating...' : 'Create Invoice'}
            </button>
            <Link
              to="/invoices"
              className="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-medium"
            >
              Cancel
            </Link>
          </div>
        </form>
      </div>
    </div>
  )
}


