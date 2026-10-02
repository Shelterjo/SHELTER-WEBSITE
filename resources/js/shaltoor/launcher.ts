// شلتور — the launcher (M69, in the main bundle: a few lines). Without JavaScript the launcher stays hidden and nothing
// changes. With it, the launcher appears; the dialog itself opens natively (Invoker Commands, fallback in ui/dialog.ts).
// The conversation script (./widget) is fetched once — on the first hover, focus or tap — so pages that never use the
// assistant never download it. Questions asked before it arrives wait for it instead of posting the form.
import { track } from '../ui/track';
import type { AskSource, Shaltoor } from './widget';

export function installShaltoor(root: HTMLElement): void {
    const launcher = root.querySelector<HTMLButtonElement>('[data-shaltoor-launcher]');
    const form = root.querySelector<HTMLFormElement>('[data-shaltoor-form]');
    if (launcher === null || form === null) return;

    let widget: Promise<Shaltoor> | null = null;
    const load = (): Promise<Shaltoor> =>
        (widget ??= import('./widget').then(({ createShaltoor }) => createShaltoor(root)));
    const ask = (question: string, source: AskSource): void => {
        void load().then((chat) => chat.ask(question, source));
    };
    const params = { language: document.documentElement.lang || 'ar', page_type: root.dataset['page'] ?? 'default' };

    launcher.hidden = false;
    launcher.addEventListener('pointerenter', () => void load(), { once: true });
    launcher.addEventListener('focus', () => void load(), { once: true });
    launcher.addEventListener('click', () => {
        void load();
        track('shaltoor_open', params);
    });
    root.addEventListener('click', (event) => {
        if (!(event.target instanceof Element)) return;
        const chip = event.target.closest<HTMLButtonElement>('[data-shaltoor-ask]');
        if (chip === null) return;
        track('shaltoor_quick_action', params);
        ask(chip.textContent ?? '', 'suggestion');
    });
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const input = form.querySelector<HTMLInputElement>('input[name="question"]');
        ask(input?.value ?? '', 'typed');
    });
}
