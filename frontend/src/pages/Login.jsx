import { useState } from 'react'
import { login } from '../api/api'
import { useNavigate, Link } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

export default function Login() {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState(null)
  const navigate = useNavigate()
  const { setUser } = useAuth()

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true)
    setError(null)
      try {
      const res = await login(email, password)
      if (res && res.token) {
        // update auth context and redirect
        setUser(res.user)
        navigate('/')
      } else {
        setError('Login failed')
      }
    } catch (err) {
      setError(err?.response?.data?.message || 'Login error')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="max-w-md mx-auto mt-12">
      <h2 className="text-2xl font-bold mb-4">Login</h2>
      {error && <div className="bg-red-50 text-red-800 p-3 rounded mb-4">{error}</div>}
      <form onSubmit={handleSubmit} className="space-y-4">
        <div>
          <label className="block text-sm font-medium text-gray-700">Email</label>
          <input value={email} onChange={(e) => setEmail(e.target.value)} className="mt-1 block w-full rounded border px-3 py-2" />
        </div>
        <div>
          <label className="block text-sm font-medium text-gray-700">Password</label>
          <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} className="mt-1 block w-full rounded border px-3 py-2" />
        </div>
        <div>
          <button type="submit" className="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
            {loading ? 'Logging in...' : 'Login'}
          </button>
        </div>
        <div className="text-center">
          <Link to="/register" className="text-sm text-indigo-600 hover:underline">Don't have an account? Register</Link>
        </div>
      </form>
    </div>
  )
}
