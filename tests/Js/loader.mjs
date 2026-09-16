export async function resolve(specifier, context, nextResolve) {
    if (specifier === '@hotwired/stimulus') {
        return {
            shortCircuit: true,
            url: new URL('../../assets/vendor/@hotwired/stimulus/stimulus.index.js', import.meta.url).href,
        };
    }
    return nextResolve(specifier, context);
}
