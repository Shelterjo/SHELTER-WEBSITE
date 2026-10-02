// Section reveal on scroll (MOTION-014, M40 §27): sections below the fold fade and rise once as they enter the
// viewport. Content is never hidden without JavaScript, never hidden for reduced motion, and sections already on
// screen at load are left alone (no flash). Motion is imported lazily, only on pages that have something to reveal,
// and only the functions used (M40 §45). The hero headline moves with CSS (resources/css/components/hero.css).

/** "700ms" | "0.7s" → seconds; falls back when the token is missing or unparsable. */
export function toSeconds(value: string, fallback: number): number {
    const match = /^\s*(-?\d*\.?\d+)\s*(ms|s)\s*$/.exec(value);
    if (match === null) return fallback;
    const amount = Number(match[1]);
    return match[2] === 'ms' ? amount / 1000 : amount;
}

/** Only elements that start below the fold are prepared for a reveal. */
export function startsBelowFold(top: number, viewportHeight: number): boolean {
    return top >= viewportHeight;
}

export async function installReveal(doc: Document = document): Promise<void> {
    const view = doc.defaultView;
    if (view === null || view.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const targets = Array.from(doc.querySelectorAll<HTMLElement>('[data-ui-reveal]')).filter((element) =>
        startsBelowFold(element.getBoundingClientRect().top, view.innerHeight),
    );
    if (targets.length === 0) return;

    const { animate, inView } = await import('motion');
    const tokens = view.getComputedStyle(doc.documentElement);
    const duration = toSeconds(tokens.getPropertyValue('--motion-reveal'), 0.6);
    const rise = tokens.getPropertyValue('--space-4').trim() || '1rem';

    for (const element of targets) {
        element.style.opacity = '0';
        inView(
            element,
            () => {
                animate(
                    element,
                    { opacity: [0, 1], transform: [`translateY(${rise})`, 'translateY(0)'] },
                    {
                        duration,
                        ease: [0.2, 0.8, 0.2, 1],
                    },
                );
            },
            // Any visible part starts the reveal (FINAL-QA QA-018): with a share (0.15) a section taller than ~6.7 screens
            // never reached it and stayed invisible.
            { amount: 0 },
        );
    }
}
