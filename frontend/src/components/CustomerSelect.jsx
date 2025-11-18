
import { useState, useEffect, useRef } from 'react'
import { getUsers, createUser } from '../api/api'

export default function CustomerSelect({ value, onChange, placeholder = 'Select or create customer' }) {
    const [query, setQuery] = useState('')
    const [results, setResults] = useState([])
    const [initialResults, setInitialResults] = useState([])
    const [loading, setLoading] = useState(false)
    const [showDropdown, setShowDropdown] = useState(false)
    const [creating, setCreating] = useState(false)
    const debounceRef = useRef(null)

    // Load initial users once on mount
    useEffect(() => {
        let mounted = true
        const loadInitial = async () => {
            setLoading(true)
            try {
                const data = await getUsers('')
                if (!mounted) return
                setInitialResults(data || [])
                setResults(data || [])
            } catch (e) {
                console.error('Failed to load initial users', e)
            } finally {
                if (mounted) setLoading(false)
            }
        }
        loadInitial()
        return () => { mounted = false }
    }, [])

    // Search when query changes; if query is empty, show initialResults
    useEffect(() => {
        if (!query) {
            setResults(initialResults)
            setLoading(false)
            return
        }

        setLoading(true)
        if (debounceRef.current) clearTimeout(debounceRef.current)
        debounceRef.current = setTimeout(async () => {
            try {
                const data = await getUsers(query)
                setResults(data)
            } catch (e) {
                console.error('Customer search failed', e)
            } finally {
                setLoading(false)
            }
        }, 300)

        return () => {
            if (debounceRef.current) clearTimeout(debounceRef.current)
        }
    }, [query, initialResults])

    const handleSelect = (cust) => {
        onChange && onChange(cust)
        setQuery(cust.name)
        setShowDropdown(false)
    }

    const handleCreate = async () => {
        if (!query.trim()) return
        setCreating(true)
        try {
            // create a new user (customer) - minimal fields
            const created = await createUser({ name: query.trim(), email: null })
            handleSelect(created)
            // add to initial results so it appears when clearing search
            setInitialResults(prev => [created, ...prev])
        } catch (e) {
            console.error('Create customer failed', e)
            alert('Failed to create customer')
        } finally {
            setCreating(false)
        }
    }

    return (
        <div className="relative">
            <input
                type="text"
                value={query || (value ? value.name : '')}
                onChange={(e) => { setQuery(e.target.value); setShowDropdown(true) }}
                onFocus={() => setShowDropdown(true)}
                placeholder={placeholder}
                className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border"
            />

            {showDropdown && (
                <div className="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-auto">
                    {loading && <div className="p-2 text-sm text-gray-500">Searching...</div>}
                    {!loading && results.length === 0 && (
                        <div className="p-2">
                            <div className="flex items-center justify-between">
                                <div className="text-sm text-gray-600">No matches</div>
                                <button
                                    onClick={handleCreate}
                                    disabled={creating}
                                    className="ml-2 px-2 py-1 text-sm bg-green-600 text-white rounded"
                                >
                                    {creating ? 'Creating...' : 'Create'}
                                </button>
                            </div>
                        </div>
                    )}

                    {!loading && results.map((r) => (
                        <div
                            key={r.id}
                            onClick={() => handleSelect(r)}
                            className="p-2 hover:bg-gray-50 cursor-pointer flex justify-between items-center"
                        >
                            <div>
                                <div className="text-sm font-medium text-gray-900">{r.name}</div>
                                {r.email && <div className="text-xs text-gray-500">{r.email}</div>}
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    )
}
