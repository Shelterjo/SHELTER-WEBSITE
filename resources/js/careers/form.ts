// Careers form (CAREERS-REQUIREMENTS §4, RECRUITMENT-SECURITY §2): progressive enhancement over a form that works
// without JavaScript. Each chosen or dropped file uploads on its own with visible progress; a failed file never clears
// the form or the other files and can be retried; the CV badge / "which file is the CV?" follow the server's decision
// (never a guess here); the submit button is disabled while files upload and while sending (no double submit).
// No personal data leaves this page except to the SHELTER server; nothing goes to analytics.

export interface CvState {
    primary: number | null;
    state: 'auto_single' | 'auto_confident' | 'needs_choice' | 'none';
}

interface UploadedFileJson {
    id: number;
    name: string;
}

interface Labels {
    cvBadge: string;
    remove: string;
    retry: string;
    network: string;
    submitting: string;
}

export function readLabels(form: HTMLElement): Labels {
    const data = form.dataset;
    return {
        cvBadge: data.labelCv ?? '',
        remove: data.labelRemove ?? '',
        retry: data.labelRetry ?? '',
        network: data.labelNetwork ?? '',
        submitting: data.labelSubmitting ?? '',
    };
}

/** The remove label carries the file name (":name" placeholder) so every button has a distinct accessible name. */
export function withName(template: string, name: string): string {
    return template.replace(':name', name);
}

function icon(name: string): SVGSVGElement | null {
    const source = document.querySelector<HTMLTemplateElement>(`template[data-careers-icon="${name}"]`);
    const svg = source?.content.firstElementChild;
    return svg instanceof SVGSVGElement ? (svg.cloneNode(true) as SVGSVGElement) : null;
}

export function installCareersForm(form: HTMLFormElement): void {
    const labels = readLabels(form);
    const uploadUrl = form.dataset.uploadUrl ?? '';
    const token = form.querySelector<HTMLInputElement>('input[name="_token"]')?.value ?? '';
    const input = form.querySelector<HTMLInputElement>('#files-input');
    const list = form.querySelector<HTMLUListElement>('[data-careers-files]');
    const drop = form.querySelector<HTMLElement>('[data-careers-drop]');
    const choice = form.querySelector<HTMLElement>('[data-careers-cv-choice]');
    const choiceOptions = form.querySelector<HTMLElement>('[data-careers-cv-options]');
    const submit = form.querySelector<HTMLButtonElement>('[data-careers-submit]');
    if (input === null || list === null || uploadUrl === '') return;

    let pending = 0;
    const syncSubmit = (): void => {
        if (submit !== null) submit.disabled = pending > 0;
    };

    const applyCv = (cv: CvState): void => {
        list.querySelectorAll<HTMLElement>('.ui-upload__item[data-file-id]').forEach((item) => {
            const isPrimary = Number(item.dataset.fileId) === cv.primary;
            let badge = item.querySelector<HTMLElement>('[data-careers-cv-badge], .ui-badge');
            if (isPrimary && badge === null) {
                badge = document.createElement('span');
                badge.className = 'ui-badge';
                badge.dataset.careersCvBadge = '';
                badge.textContent = labels.cvBadge;
                item.querySelector('.ui-upload__name')?.after(badge);
            } else if (!isPrimary && badge !== null) {
                badge.remove();
            }
        });
        if (choice === null || choiceOptions === null) return;
        choice.hidden = cv.state !== 'needs_choice';
        if (cv.state !== 'needs_choice') return;
        const checked = choiceOptions.querySelector<HTMLInputElement>('input:checked')?.value;
        choiceOptions.replaceChildren(
            ...Array.from(list.querySelectorAll<HTMLElement>('.ui-upload__item[data-file-id]')).map((item) => {
                const id = item.dataset.fileId ?? '';
                const label = document.createElement('label');
                label.className = 'ui-check';
                const radio = document.createElement('input');
                radio.type = 'radio';
                radio.name = 'primary_attachment';
                radio.value = id;
                radio.id = `cv-${id}`;
                radio.className = 'ui-check__input';
                radio.checked = checked === id;
                const text = document.createElement('span');
                text.className = 'ui-check__text';
                text.textContent = item.querySelector('.ui-upload__name')?.textContent ?? '';
                label.append(radio, text);
                return label;
            }),
        );
    };

    const removeButton = (item: HTMLElement, name: string): HTMLButtonElement => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'ui-button ui-button--ghost ui-button--icon-only ui-button--sm';
        button.setAttribute('aria-label', withName(labels.remove, name));
        const svg = icon('x');
        if (svg !== null) button.append(svg);
        button.addEventListener('click', () => void remove(item));
        return button;
    };

    const remove = async (item: HTMLElement): Promise<void> => {
        const id = item.dataset.fileId;
        if (id === undefined) {
            item.remove();
            return;
        }
        try {
            const response = await fetch(`${uploadUrl}${id}/`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': token, Accept: 'application/json' },
                credentials: 'same-origin',
            });
            if (!response.ok) return;
            const body = (await response.json()) as { cv: CvState };
            item.remove();
            applyCv(body.cv);
        } catch {
            // The file stays listed; the applicant can try again.
        }
    };

    const upload = (file: File, item?: HTMLLIElement): void => {
        const row = item ?? document.createElement('li');
        row.className = 'ui-upload__item';
        delete row.dataset.fileId;
        const name = document.createElement('span');
        name.className = 'ui-upload__name';
        name.dir = 'auto';
        name.textContent = file.name;
        const progress = document.createElement('progress');
        progress.className = 'ui-upload__progress';
        progress.max = 100;
        progress.value = 0;
        const fileIcon = icon('file-text');
        row.replaceChildren(...(fileIcon !== null ? [fileIcon] : []), name, progress);
        if (item === undefined) list.append(row);

        pending++;
        syncSubmit();
        const body = new FormData();
        body.append('file', file);
        body.append('_token', token);
        const request = new XMLHttpRequest();
        request.open('POST', uploadUrl);
        request.setRequestHeader('Accept', 'application/json');
        request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        request.upload.addEventListener('progress', (event) => {
            if (event.lengthComputable) progress.value = Math.round((event.loaded / event.total) * 100);
        });
        const fail = (message: string, retry: boolean): void => {
            progress.remove();
            row.classList.add('ui-upload__item--error');
            const error = document.createElement('p');
            error.className = 'ui-upload__error';
            error.setAttribute('role', 'alert');
            error.textContent = message;
            row.append(removeButton(row, file.name), error);
            if (retry) {
                const again = document.createElement('button');
                again.type = 'button';
                again.className = 'ui-button ui-button--secondary ui-button--sm';
                again.textContent = labels.retry;
                again.addEventListener('click', () => upload(file, row));
                error.append(again);
            }
        };
        request.addEventListener('load', () => {
            pending--;
            syncSubmit();
            let json: { file?: UploadedFileJson; cv?: CvState; message?: string } = {};
            try {
                json = JSON.parse(request.responseText) as typeof json;
            } catch {
                json = {};
            }
            if (request.status === 201 && json.file !== undefined && json.cv !== undefined) {
                progress.remove();
                row.classList.remove('ui-upload__item--error');
                row.dataset.fileId = String(json.file.id);
                row.append(removeButton(row, json.file.name));
                applyCv(json.cv);
                // A file is on the server now: the earlier "attach your CV" message no longer applies.
                form.querySelector('#files-error')?.remove();
                return;
            }
            fail(json.message ?? labels.network, request.status === 0 || request.status >= 500);
        });
        request.addEventListener('error', () => {
            pending--;
            syncSubmit();
            fail(labels.network, true);
        });
        request.send(body);
    };

    const take = (files: FileList | null): void => {
        if (files === null) return;
        Array.from(files).forEach((file) => upload(file));
        input.value = ''; // the files are on the server now; they must not travel again with the form
    };

    input.addEventListener('change', () => take(input.files));
    if (drop !== null) {
        drop.addEventListener('dragover', (event) => {
            event.preventDefault();
            drop.classList.add('is-dragover');
        });
        drop.addEventListener('dragleave', () => drop.classList.remove('is-dragover'));
        drop.addEventListener('drop', (event) => {
            event.preventDefault();
            drop.classList.remove('is-dragover');
            take(event.dataTransfer?.files ?? null);
        });
    }

    // Files the server already holds (after a failed submit) get their remove buttons.
    list.querySelectorAll<HTMLElement>('.ui-upload__item[data-file-id]').forEach((item) => {
        const button = item.querySelector<HTMLButtonElement>('[data-careers-remove]');
        if (button === null) return;
        button.hidden = false;
        button.addEventListener('click', () => void remove(item));
    });

    form.addEventListener('submit', (event) => {
        if (pending > 0) {
            event.preventDefault();
            return;
        }
        if (submit !== null) {
            submit.disabled = true;
            submit.setAttribute('aria-busy', 'true');
            submit.textContent = labels.submitting;
        }
    });
}
