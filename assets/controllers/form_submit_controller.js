import { Controller } from '@hotwired/stimulus';

/**
 * Retorna todos os botões de envio associados a um formulário.
 * Inclui botões dentro do <form> e botões externos com o atributo form="id".
 *
 * @param {HTMLFormElement} form
 * @returns {HTMLElement[]}
 */
export function getSubmitButtons(form) {
    if (!form || !(form instanceof HTMLFormElement)) {
        return [];
    }

    const inside = Array.from(
        form.querySelectorAll('button[type="submit"], input[type="submit"], button:not([type])')
    );

    let outside = [];
    if (form.id) {
        outside = Array.from(
            document.querySelectorAll(
                `button[form="${form.id}"][type="submit"], button[form="${form.id}"]:not([type]), input[form="${form.id}"][type="submit"]`
            )
        );
    }

    return Array.from(new Set([...inside, ...outside]));
}

/**
 * Desativa um botão de envio e adiciona feedback visual (spinner),
 * preservando o estado original para restauração posterior.
 *
 * @param {HTMLButtonElement|HTMLInputElement} btn
 */
export function disableButton(btn) {
    if (!btn || btn.dataset.formSubmitDisabled === 'true') {
        return;
    }

    btn.dataset.formSubmitDisabled = 'true';
    btn.dataset.originalHtml = btn.innerHTML;
    btn.setAttribute('aria-disabled', 'true');

    // Preserva a largura atual do botão para evitar "pulo" de layout (layout shift)
    if (typeof btn.getBoundingClientRect === 'function') {
        const rect = btn.getBoundingClientRect();
        if (rect && rect.width > 0) {
            btn.style.minWidth = `${Math.ceil(rect.width)}px`;
        }
    }

    // Cria o indicador de carregamento (spinner do Bootstrap)
    if (typeof document !== 'undefined') {
        const spinner = document.createElement('span');
        spinner.className = 'spinner-border spinner-border-sm';
        spinner.setAttribute('role', 'status');
        spinner.setAttribute('aria-hidden', 'true');

        const hasText = btn.textContent && btn.textContent.trim().length > 0;
        if (hasText) {
            spinner.classList.add('me-1');
        }

        // Se houver um ícone Bootstrap (<i class="bi ...">), substitui pelo spinner;
        // caso contrário, insere o spinner no início do botão.
        const existingIcon = btn.querySelector ? btn.querySelector('i.bi') : null;
        if (existingIcon && typeof existingIcon.replaceWith === 'function') {
            existingIcon.replaceWith(spinner);
        } else if (typeof btn.prepend === 'function') {
            btn.prepend(spinner);
        }
    }

    btn.disabled = true;
}

/**
 * Restaura um botão de envio para o seu estado e conteúdo originais.
 *
 * @param {HTMLButtonElement|HTMLInputElement} btn
 */
export function enableButton(btn) {
    if (!btn || btn.dataset.formSubmitDisabled !== 'true') {
        return;
    }

    if (btn.dataset.originalHtml !== undefined) {
        btn.innerHTML = btn.dataset.originalHtml;
        delete btn.dataset.originalHtml;
    }

    btn.style.minWidth = '';
    btn.disabled = false;
    btn.removeAttribute('aria-disabled');
    delete btn.dataset.formSubmitDisabled;
}

/**
 * Desativa todos os botões de envio do formulário especificado.
 *
 * @param {HTMLFormElement} form
 */
export function disableSubmitButtons(form) {
    if (!form || !(form instanceof HTMLFormElement)) return;
    if (form.dataset.submitting === 'true') return;

    form.dataset.submitting = 'true';
    const buttons = getSubmitButtons(form);
    buttons.forEach((btn) => disableButton(btn));
}

/**
 * Reativa todos os botões de envio do formulário especificado.
 *
 * @param {HTMLFormElement} form
 */
export function enableSubmitButtons(form) {
    if (!form || !(form instanceof HTMLFormElement)) return;

    delete form.dataset.submitting;
    const buttons = getSubmitButtons(form);
    buttons.forEach((btn) => enableButton(btn));
}

/**
 * Verifica se o formulário deve ser gerenciado pela proteção contra múltiplos envios.
 *
 * @param {HTMLFormElement} form
 * @returns {boolean}
 */
export function shouldHandleForm(form) {
    if (!form || !(form instanceof HTMLFormElement)) return false;
    if (form.dataset.preventDoubleSubmit === 'false' || form.dataset.disableOnSubmit === 'false') {
        return false;
    }
    return true;
}

/**
 * Handler do evento 'submit' tradicional / nativo.
 *
 * @param {SubmitEvent} event
 */
export function handleSubmit(event) {
    const form = event.target;
    if (!shouldHandleForm(form)) return;

    // Se o envio foi cancelado (ex: confirm() retornou false), não desativa
    if (event.defaultPrevented) {
        enableSubmitButtons(form);
        return;
    }

    // TOME CUIDADO QUANDO O FORMULÁRIO ESTIVER INVÁLIDO:
    // Se a validação client-side HTML5 estiver ativa (sem o atributo 'novalidate')
    // e o formulário não for válido, o navegador NÃO fará a requisição.
    // Portanto, NÃO desativa os botões para permitir que o usuário corrija os campos.
    if (!form.noValidate && typeof form.checkValidity === 'function' && !form.checkValidity()) {
        enableSubmitButtons(form);
        return;
    }

    // Se já estiver em processo de envio, bloqueia requisições duplicadas
    if (form.dataset.submitting === 'true') {
        event.preventDefault();
        if (typeof event.stopImmediatePropagation === 'function') {
            event.stopImmediatePropagation();
        }
        return;
    }

    // Desativa no próximo tick para permitir que o navegador capture os dados do submitter
    // em requisições HTTP tradicionais
    setTimeout(() => {
        if (!event.defaultPrevented) {
            disableSubmitButtons(form);
        }
    }, 0);
}

/**
 * Handler do evento 'turbo:submit-start' disparado pelo Hotwire Turbo
 * quando a requisição fetch é iniciada.
 *
 * @param {CustomEvent} event
 */
export function handleTurboSubmitStart(event) {
    const form = event.detail?.formSubmission?.formElement || event.target;
    if (!shouldHandleForm(form)) return;

    disableSubmitButtons(form);
}

/**
 * Handler do evento 'turbo:submit-end' disparado pelo Hotwire Turbo
 * quando a requisição termina (sucesso, erro 422 de validação, 500, etc.).
 *
 * @param {CustomEvent} event
 */
export function handleTurboSubmitEnd(event) {
    const form = event.detail?.formSubmission?.formElement || event.target;
    if (!form) return;

    // Se o formulário retornou inválido (ex: status 422), erro de rede ou erro 500,
    // reativa os botões imediatamente para permitir nova submissão.
    enableSubmitButtons(form);
}

/**
 * Handler disparado quando um campo do formulário falha na validação HTML5.
 *
 * @param {Event} event
 */
export function handleInvalid(event) {
    const form = event.target?.form;
    if (form) {
        enableSubmitButtons(form);
    }
}

/**
 * Handler disparado quando o usuário altera qualquer campo após um erro.
 *
 * @param {Event} event
 */
export function handleInput(event) {
    const form = event.target?.form;
    if (form && form.dataset.submitting === 'true') {
        enableSubmitButtons(form);
    }
}

/**
 * Inicializa os ouvintes de eventos globais no documento e na janela.
 */
export function registerFormSubmitProtection() {
    if (typeof window === 'undefined' || typeof document === 'undefined') return;

    if (window.__formSubmitProtectionRegistered) return;
    window.__formSubmitProtectionRegistered = true;

    // Eventos de envio
    document.addEventListener('submit', handleSubmit);
    document.addEventListener('turbo:submit-start', handleTurboSubmitStart);
    document.addEventListener('turbo:submit-end', handleTurboSubmitEnd);
    document.addEventListener('turbo:fetch-request-error', handleTurboSubmitEnd);

    // Proteção quando campos são inválidos
    document.addEventListener('invalid', handleInvalid, true);

    // Reativação quando o usuário interage ou a página volta do histórico (bfcache)
    document.addEventListener('input', handleInput, true);
    document.addEventListener('change', handleInput, true);
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('form').forEach(enableSubmitButtons);
    });
}

// Inicialização automática ao carregar o módulo no navegador
if (typeof window !== 'undefined' && typeof document !== 'undefined') {
    registerFormSubmitProtection();
}

/**
 * Controlador Stimulus para integração declarativa opcional com data-controller="form-submit".
 */
/* stimulusFetch: 'eager' */
export default class FormSubmitController extends Controller {
    connect() {
        registerFormSubmitProtection();
    }

    disconnect() {
        if (this.element instanceof HTMLFormElement) {
            enableSubmitButtons(this.element);
        }
    }

    submit(event) {
        handleSubmit(event);
    }
}
