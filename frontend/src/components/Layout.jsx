import { Link, Outlet, useLocation } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

export default function Layout() {
  const location = useLocation()

  const isActive = (path) => {
    return location.pathname === path || location.pathname.startsWith(path + '/')
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <nav className="bg-white shadow-sm border-b border-gray-200">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between h-16">
            <div className="flex">
              <div className="flex-shrink-0 flex items-center">
                <h1 className="text-xl font-bold text-gray-900">Invoice System</h1>
              </div>
              <div className="hidden sm:ml-8 sm:flex sm:space-x-8">
                <Link
                  to="/"
                  className={`${
                    isActive('/') && location.pathname === '/'
                      ? 'border-indigo-500 text-gray-900'
                      : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
                  } inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium`}
                >
                  Dashboard
                </Link>
                <Link
                  to="/invoices"
                  className={`${
                    isActive('/invoices')
                      ? 'border-indigo-500 text-gray-900'
                      : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
                  } inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium`}
                >
                  Invoices
                </Link>
              </div>
            </div>
            <div className="flex items-center space-x-4">
              {/* Signed-in user info + logout/login links */}
              <AuthInfo />
            </div>
          </div>
          </div>
      </nav>

      <main className="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <Outlet />
      </main>
    </div>
  )
}

function AuthInfo() {
  const { user, signOut } = useAuth()

  if (!user) {
    return (
      <div className="hidden sm:flex sm:items-center sm:space-x-4">
        <Link to="/login" className="text-sm text-gray-700 hover:text-gray-900">Sign in</Link>
        <Link to="/register" className="text-sm text-indigo-600 hover:text-indigo-800">Register</Link>
      </div>
    )
  }

  return (
    <div className="hidden sm:flex sm:items-center sm:space-x-4">
      <div className="text-sm text-gray-700">
        <div className="font-medium">{user.name}</div>
        <div className="text-xs text-gray-500">{user.email} {user.role ? `• ${user.role}` : ''}</div>
      </div>
      <button
        onClick={() => signOut()}
        className="px-3 py-1 bg-gray-100 text-sm rounded-md hover:bg-gray-200"
      >
        Logout
      </button>
    </div>
  )
}


