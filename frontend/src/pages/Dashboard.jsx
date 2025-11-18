import { useQuery } from '@tanstack/react-query'
import { getSummary } from '../api/api'
import { Link } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

export default function Dashboard() {
  const { data: summary, isLoading, error } = useQuery({
    queryKey: ['summary'],
    queryFn: getSummary,
  })

  const { user } = useAuth()
  const isAdmin = user?.role === 'admin'

  if (isLoading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="bg-red-50 border border-red-200 rounded-lg p-4">
        <p className="text-red-800">Error loading dashboard: {error.message}</p>
      </div>
    )
  }

  const stats = [
    {
      name: 'Total Invoices',
      value: summary?.total_invoices || 0,
      icon: '📄',
      color: 'bg-blue-500',
    },
    {
      name: 'Paid Invoices',
      value: summary?.total_paid || 0,
      icon: '✅',
      color: 'bg-green-500',
    },
    {
      name: 'Unpaid Invoices',
      value: summary?.total_unpaid || 0,
      icon: '⏳',
      color: 'bg-yellow-500',
    },
    {
      name: 'Total Revenue',
      value: `$${parseFloat(summary?.total_revenue || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })}`,
      icon: '💰',
      color: 'bg-indigo-500',
    },
    {
      name: 'Outstanding Amount',
      value: `$${parseFloat(summary?.total_outstanding || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })}`,
      icon: '💳',
      color: 'bg-red-500',
    },
  ]

  // If not admin, hide admin-only stats for the dashboard UI
  const visibleStats = isAdmin ? stats : stats.filter(s => ['Paid Invoices', 'Unpaid Invoices'].includes(s.name))

  return (
    <div className="px-4 sm:px-0">
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900">Dashboard</h1>
        <p className="mt-2 text-gray-600">Overview of your invoicing system</p>
      </div>

      <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        {visibleStats.map((stat) => (
          <div
            key={stat.name}
            className="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow"
          >
            <div className="p-5">
              <div className="flex items-center">
                <div className={`flex-shrink-0 ${stat.color} rounded-md p-3`}>
                  <span className="text-2xl">{stat.icon}</span>
                </div>
                <div className="ml-5 w-0 flex-1">
                  <dl>
                    <dt className="text-sm font-medium text-gray-500 truncate">
                      {stat.name}
                    </dt>
                    <dd className="text-2xl font-semibold text-gray-900">
                      {stat.value}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        ))}
      </div>

      <div className="mt-8 bg-white shadow rounded-lg p-6">
        <h2 className="text-xl font-semibold text-gray-900 mb-4">Quick Actions</h2>
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
          {isAdmin && (
            <Link
              to="/invoices/create"
              className="inline-flex items-center justify-center px-4 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition-colors"
            >
              <span className="mr-2">➕</span>
              Create New Invoice
            </Link>
          )}
          <Link
            to="/invoices"
            className="inline-flex items-center justify-center px-4 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors"
          >
            <span className="mr-2">📋</span>
            View All Invoices
          </Link>
        </div>
      </div>
      {/* Only show detailed revenue breakdown to admins */}
      {isAdmin && summary?.revenue_by_status && (
        <div className="mt-8 bg-white shadow rounded-lg p-6">
          <h2 className="text-xl font-semibold text-gray-900 mb-4">Revenue by Status</h2>
          <div className="space-y-3">
            {Object.entries(summary.revenue_by_status).map(([status, amount]) => (
              <div key={status} className="flex justify-between items-center">
                <span className="text-gray-700 font-medium">{status}</span>
                <span className="text-gray-900 font-semibold">
                  ${parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}
                </span>
              </div>
            ))}
          </div>
        </div>
      )}

      {!isAdmin && (
        <div className="mt-6 text-sm text-gray-600">
          Some dashboard metrics are only visible to administrators.
        </div>
      )}
    </div>
  )
}


