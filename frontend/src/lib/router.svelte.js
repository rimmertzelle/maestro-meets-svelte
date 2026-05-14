export const router = $state({ path: window.location.pathname })

export function navigate(to) {
    window.history.pushState(null, '', to)
    router.path = to
}
