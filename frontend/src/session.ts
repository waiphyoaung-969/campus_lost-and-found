const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000'

export type Member = {
  id: number
  name: string
  email: string
  role: 'member'
}

function request(path: string, init?: RequestInit) {
  const headers = new Headers(init?.headers)
  headers.set('Accept', 'application/json')

  return fetch(new URL(path, apiUrl), {
    ...init,
    credentials: 'include',
    headers,
  })
}

export async function currentMember(signal: AbortSignal): Promise<Member | null> {
  const response = await request('/api/v1/me', { signal })

  if (response.status === 401) return null
  if (!response.ok) throw new Error('Unable to check your session.')

  const { data }: { data: Member } = await response.json()
  return data
}

export async function logout(): Promise<void> {
  const csrfResponse = await request('/sanctum/csrf-cookie')
  if (!csrfResponse.ok) throw new Error('Unable to initialize CSRF protection.')

  const cookie = document.cookie.split('; ').find((value) => value.startsWith('XSRF-TOKEN='))
  if (!cookie) throw new Error('The CSRF cookie is missing.')

  const response = await request('/api/v1/logout', {
    method: 'POST',
    headers: { 'X-XSRF-TOKEN': decodeURIComponent(cookie.slice('XSRF-TOKEN='.length)) },
  })

  if (!response.ok && response.status !== 401) throw new Error('Unable to sign out.')
}
