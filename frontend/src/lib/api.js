export async function apiFetch(path, options = {}) {
    const res = await fetch(path, {
        ...options,
        headers: { 'Content-Type': 'application/json', ...(options.headers ?? {}) },
        credentials: 'same-origin',
    })
    const data = await res.json()
    if (!res.ok) throw new Error(data.error ?? 'Request failed')
    return data
}

export function post(path, body) {
    return apiFetch(path, { method: 'POST', body: JSON.stringify(body) })
}
