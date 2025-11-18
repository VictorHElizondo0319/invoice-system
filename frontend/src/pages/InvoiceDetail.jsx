import { useEffect, useState } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { useParams, useNavigate, Link } from 'react-router-dom'
import { getInvoice, submitInvoice, createPayment, deleteInvoice } from '../api/api'
import { exportInvoice } from '../api/api'
import { useAuth } from '../context/AuthContext'

export default function InvoiceDetail() {
  const { id } = useParams()
  const navigate = useNavigate()
  const queryClient = useQueryClient()
  const [paymentAmount, setPaymentAmount] = useState('')
  const [showPaymentForm, setShowPaymentForm] = useState(false)

  const { data: invoice, isLoading, error } = useQuery({
    queryKey: ['invoice', id],
    queryFn: () => getInvoice(id),
  })

  const { user } = useAuth()
  const isAdmin = user?.role === 'admin'
  const isOwner = user && invoice && (invoice.customer_id === user.id)

  useEffect(() => {
    if (!isAdmin && !isOwner) {
      navigate('/invoices')
    }
  }, [isAdmin, isOwner, navigate])

  console.log(!isAdmin || !isOwner)
  const submitMutation = useMutation({
    mutationFn: submitInvoice,
    onSuccess: () => {
      queryClient.invalidateQueries(['invoice', id])
      queryClient.invalidateQueries(['invoices'])
      queryClient.invalidateQueries(['summary'])
      alert('Invoice submitted successfully!')
    },
    onError: (error) => {
      alert(`Failed to submit invoice: ${error.response?.data?.message || error.message}`)
    },
  })

  const paymentMutation = useMutation({
    mutationFn: createPayment,
    onSuccess: () => {
      queryClient.invalidateQueries(['invoice', id])
      queryClient.invalidateQueries(['invoices'])
      queryClient.invalidateQueries(['summary'])
      setPaymentAmount('')
      setShowPaymentForm(false)
      alert('Payment processed successfully!')
    },
    onError: (error) => {
      alert(`Failed to process payment: ${error.response?.data?.message || error.message}`)
    },
  })

  const deleteMutation = useMutation({
    mutationFn: deleteInvoice,
    onSuccess: () => {
      queryClient.invalidateQueries(['invoices'])
      queryClient.invalidateQueries(['summary'])
      navigate('/invoices')
    },
    onError: (error) => {
      alert(`Failed to delete invoice: ${error.response?.data?.message || error.message}`)
    },
  })

  const exportMutation = useMutation({
    mutationFn: exportInvoice,
    onSuccess: (data) => {
      queryClient.invalidateQueries(['invoice', id])
      queryClient.invalidateQueries(['invoices'])
      if (data && data.url) {
        window.open(data.url, '_blank')
        alert('PDF uploaded to S3')
      }
    },
    onError: (error) => {
      alert(`Failed to export PDF: ${error.response?.data?.message || error.message}`)
    }
  })

  const handleSubmit = () => {
    if (window.confirm('Are you sure you want to submit this invoice?')) {
      submitMutation.mutate(id)
    }
  }

  const handlePayment = (e) => {
    e.preventDefault()
    const amount = parseFloat(paymentAmount)
    if (isNaN(amount) || amount <= 0) {
      alert('Please enter a valid amount')
      return
    }
    paymentMutation.mutate({
      invoice_id: parseInt(id),
      amount: amount,
    })
  }

  const handleDelete = () => {
    if (window.confirm('Are you sure you want to delete this invoice? This action cannot be undone.')) {
      deleteMutation.mutate(id)
    }
  }

  const getStatusColor = (status) => {
    const colors = {
      DRAFT: 'bg-gray-100 text-gray-800',
      SUBMITTED: 'bg-blue-100 text-blue-800',
      PAID: 'bg-green-100 text-green-800',
      OVERDUE: 'bg-red-100 text-red-800',
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
  }

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    })
  }

  const formatAmount = (amount) => {
    return `$${parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}`
  }

  if (isLoading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="px-4 sm:px-0">
        <div className="bg-red-50 border border-red-200 rounded-lg p-4">
          <p className="text-red-800">Error loading invoice: {error.message}</p>
        </div>
        <Link to="/invoices" className="mt-4 inline-block text-indigo-600 hover:text-indigo-800">
          ← Back to Invoices
        </Link>
      </div>
    )
  }

  const outstandingAmount = invoice.total_amount - invoice.paid_amount

  return (
    <div className="px-4 sm:px-0">
      <div className="mb-4">
        <Link to="/invoices" className="text-indigo-600 hover:text-indigo-800 text-sm">
          ← Back to Invoices
        </Link>
      </div>

      <div className="bg-white shadow rounded-lg">
        <div className="px-6 py-4 border-b border-gray-200">
          <div className="flex justify-between items-start">
            <div>
              <h1 className="text-2xl font-bold text-gray-900">Invoice #{invoice.id}</h1>
              <p className="mt-1 text-sm text-gray-500">
                Created: {formatDate(invoice.created_at)}
              </p>
            </div>
            <span className={`px-3 py-1 text-sm font-semibold rounded-full ${getStatusColor(invoice.status)}`}>
              {invoice.status}
            </span>
          </div>
        </div>

        <div className="px-6 py-4 space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h3 className="text-sm font-medium text-gray-500">Customer</h3>
              <p className="mt-1 text-lg font-semibold text-gray-900">{invoice.customer_name}</p>
            </div>
            <div>
              <h3 className="text-sm font-medium text-gray-500">Due Date</h3>
              <p className="mt-1 text-lg font-semibold text-gray-900">{formatDate(invoice.due_date)}</p>
            </div>
          </div>

          <div>
            <h3 className="text-lg font-semibold text-gray-900 mb-3">Line Items</h3>
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                    <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Price</th>
                    <th className="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                  </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                  {invoice.items.map((item) => (
                    <tr key={item.id}>
                      <td className="px-4 py-3 text-sm text-gray-900">{item.description}</td>
                      <td className="px-4 py-3 text-sm text-gray-900 text-right">{item.qty}</td>
                      <td className="px-4 py-3 text-sm text-gray-900 text-right">{formatAmount(item.price)}</td>
                      <td className="px-4 py-3 text-sm font-medium text-gray-900 text-right">
                        {formatAmount(item.qty * item.price)}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>

          <div className="border-t pt-4">
            <div className="flex justify-end space-y-2 flex-col items-end">
              <div className="flex justify-between w-64">
                <span className="text-gray-700">Total Amount:</span>
                <span className="font-semibold text-gray-900">{formatAmount(invoice.total_amount)}</span>
              </div>
              <div className="flex justify-between w-64">
                <span className="text-gray-700">Paid Amount:</span>
                <span className="font-semibold text-green-600">{formatAmount(invoice.paid_amount)}</span>
              </div>
              <div className="flex justify-between w-64 text-lg">
                <span className="font-semibold text-gray-900">Outstanding:</span>
                <span className="font-bold text-red-600">{formatAmount(outstandingAmount)}</span>
              </div>
            </div>
          </div>

          {invoice.payments && invoice.payments.length > 0 && (
            <div>
              <h3 className="text-lg font-semibold text-gray-900 mb-3">Payment History</h3>
              <div className="space-y-2">
                {invoice.payments.map((payment) => (
                  <div key={payment.id} className="flex justify-between items-center bg-gray-50 p-3 rounded">
                    <div>
                      <p className="text-sm font-medium text-gray-900">{formatAmount(payment.amount)}</p>
                      <p className="text-xs text-gray-500">{formatDate(payment.created_at)}</p>
                    </div>
                    <span className="text-xs text-gray-600">TXN: {payment.transaction_id}</span>
                  </div>
                ))}
              </div>
            </div>
          )}

          <div className="flex flex-wrap gap-3 pt-4 border-t">
            {invoice.status === 'DRAFT' && isAdmin && (
              <>
                <button
                  onClick={handleSubmit}
                  disabled={submitMutation.isPending}
                  className="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                >
                  {submitMutation.isPending ? 'Submitting...' : 'Submit Invoice'}
                </button>
                <button
                  onClick={handleDelete}
                  disabled={deleteMutation.isPending}
                  className="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50"
                >
                  {deleteMutation.isPending ? 'Deleting...' : 'Delete Invoice'}
                </button>
              </>
            )}
            {!isAdmin && (invoice.status === 'SUBMITTED' || (invoice.status === 'PAID' && outstandingAmount > 0)) && (
              <button
                onClick={() => setShowPaymentForm(!showPaymentForm)}
                className="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
              >
                {showPaymentForm ? 'Cancel Payment' : 'Make Payment'}
              </button>
            )}
            <div className="ml-auto">
              {(invoice.pdf_url || invoice.pdf_temp_url) ? (
                <a href={invoice.pdf_url || invoice.pdf_temp_url} target="_blank" rel="noreferrer" className="px-4 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-800">
                  View PDF
                </a>
              ) : (
                // Only allow export for admins or the invoice owner
                (isAdmin || isOwner) ? (
                  <button
                    onClick={() => exportMutation.mutate(id)}
                    disabled={exportMutation.isLoading}
                    className="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700"
                  >
                    {exportMutation.isLoading ? 'Exporting...' : 'Export & Upload PDF'}
                  </button>
                ) : (
                  <span className="text-sm text-gray-500">PDF not available</span>
                )
              )}
            </div>
          </div>

          {showPaymentForm && (
            <div className="bg-blue-50 p-4 rounded-lg">
              <h3 className="text-lg font-semibold text-gray-900 mb-3">Process Payment</h3>
              <form onSubmit={handlePayment} className="space-y-4">
                <div>
                  <label htmlFor="amount" className="block text-sm font-medium text-gray-700 mb-1">
                    Payment Amount
                  </label>
                  <div className="relative">
                    <span className="absolute left-3 top-2 text-gray-500">$</span>
                    <input
                      type="number"
                      id="amount"
                      value={paymentAmount}
                      onChange={(e) => setPaymentAmount(e.target.value)}
                      step="0.01"
                      min="0.01"
                      max={outstandingAmount}
                      placeholder="0.00"
                      className="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border"
                      required
                    />
                  </div>
                  <p className="mt-1 text-xs text-gray-500">
                    Maximum: {formatAmount(outstandingAmount)}
                  </p>
                </div>
                <button
                  type="submit"
                  disabled={paymentMutation.isPending}
                  className="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50"
                >
                  {paymentMutation.isPending ? 'Processing...' : 'Process Payment'}
                </button>
              </form>
            </div>
          )}
        </div>
      </div>
    </div>
  )
}


