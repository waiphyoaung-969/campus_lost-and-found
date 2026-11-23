import { useEffect, useState } from 'react'
import { currentMember, logout, type Member } from './session'

function App() {
  const [member, setMember] = useState<Member | null>()
  const [error, setError] = useState('')
  const [signingOut, setSigningOut] = useState(false)

  useEffect(() => {
    const controller = new AbortController()

    currentMember(controller.signal).then(setMember).catch(() => {
      if (!controller.signal.aborted) setError('Could not check your session. Refresh to try again.')
    })

    return () => controller.abort()
  }, [])

  async function signOut() {
    setSigningOut(true)
    setError('')

    try {
      await logout()
      setMember(null)
    } catch {
      setError('Could not sign out. Please try again.')
    } finally {
      setSigningOut(false)
    }
  }

  return (
    <main className="min-h-screen bg-slate-50 px-6 py-16 text-slate-900">
      <div className="mx-auto flex max-w-xl flex-col items-start gap-5">
        <h1 className="text-4xl font-semibold tracking-tight">Campus Loop</h1>
        <p className="text-lg text-slate-700">Lost and found for the AIU campus.</p>
        {error && <p role="alert" className="text-red-800">{error}</p>}
        {member === undefined && !error && <p role="status">Checking your session…</p>}
        {member === null && <p>Sign-in with an AIU Microsoft account is pending setup.</p>}
        {member && (
          <>
            <p>Signed in as {member.name}.</p>
            <button
              type="button"
              onClick={signOut}
              disabled={signingOut}
              className="rounded-md bg-slate-900 px-4 py-2 font-medium text-white disabled:opacity-60"
            >
              {signingOut ? 'Signing out…' : 'Sign out'}
            </button>
          </>
        )}
      </div>
    </main>
  )
}

export default App
