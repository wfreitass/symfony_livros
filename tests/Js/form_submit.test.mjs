import { describe, it, beforeEach } from 'node:test';
import assert from 'node:assert/strict';

// Importa os métodos do controller para testar suas regras de negócio
import {
    getSubmitButtons,
    disableButton,
    enableButton,
    disableSubmitButtons,
    enableSubmitButtons,
    shouldHandleForm,
    handleSubmit,
    handleTurboSubmitStart,
    handleTurboSubmitEnd,
    handleInvalid,
    handleInput
} from '../../assets/controllers/form_submit_controller.js';

// Mocks simples para ambiente Node.js
class MockElement {
    constructor(tagName = 'div', attributes = {}) {
        this.tagName = tagName.toUpperCase();
        this.attributes = attributes;
        this.dataset = {};
        this.children = [];
        this.className = '';
        this.classList = {
            _classes: new Set(),
            add: (cls) => this.classList._classes.add(cls),
            remove: (cls) => this.classList._classes.delete(cls),
            contains: (cls) => this.classList._classes.has(cls)
        };
        this.innerHTML = '';
        this.textContent = '';
        this.disabled = false;
        this.style = {};
        this.form = null;
    }

    setAttribute(name, value) {
        this.attributes[name] = String(value);
    }

    getAttribute(name) {
        return this.attributes[name] || null;
    }

    removeAttribute(name) {
        delete this.attributes[name];
    }

    getBoundingClientRect() {
        return { width: 100, height: 38 };
    }

    prepend(child) {
        this.children.unshift(child);
        this.innerHTML = child.innerHTML + this.innerHTML;
    }

    querySelector(selector) {
        if (selector === 'i.bi') {
            return this.children.find((c) => c.className && c.className.includes('bi')) || null;
        }
        return null;
    }
}

class MockButton extends MockElement {
    constructor(attributes = {}) {
        super('BUTTON', attributes);
        this.type = attributes.type || 'submit';
    }
}

class MockForm extends MockElement {
    constructor(attributes = {}) {
        super('FORM', attributes);
        this.id = attributes.id || 'test-form';
        this.noValidate = attributes.novalidate !== undefined;
        this._validity = true;
        this._elements = [];
    }

    setValidity(valid) {
        this._validity = valid;
    }

    checkValidity() {
        return this._validity;
    }

    querySelectorAll(selector) {
        return this._elements.filter((el) => {
            if (selector.includes('button[type="submit"]')) {
                return el.tagName === 'BUTTON' && el.type === 'submit';
            }
            return false;
        });
    }
}

// Configura globais mínimos para o teste
globalThis.HTMLFormElement = MockForm;
globalThis.document = {
    createElement(tag) {
        const el = new MockElement(tag);
        return el;
    },
    querySelectorAll() {
        return [];
    }
};

describe('FormSubmitController', () => {
    let form;
    let submitBtn;

    beforeEach(() => {
        form = new MockForm();
        submitBtn = new MockButton({ type: 'submit' });
        submitBtn.innerHTML = '<i class="bi bi-check-lg"></i> Salvar';
        submitBtn.textContent = 'Salvar';
        submitBtn.form = form;
        form._elements = [submitBtn];
    });

    it('deve desativar o botão e adicionar o spinner', () => {
        disableButton(submitBtn);

        assert.equal(submitBtn.disabled, true);
        assert.equal(submitBtn.dataset.formSubmitDisabled, 'true');
        assert.equal(submitBtn.attributes['aria-disabled'], 'true');
        assert.ok(submitBtn.dataset.originalHtml.includes('Salvar'));
    });

    it('deve restaurar o botão ao seu estado original', () => {
        disableButton(submitBtn);
        assert.equal(submitBtn.disabled, true);

        enableButton(submitBtn);
        assert.equal(submitBtn.disabled, false);
        assert.equal(submitBtn.dataset.formSubmitDisabled, undefined);
        assert.equal(submitBtn.innerHTML, '<i class="bi bi-check-lg"></i> Salvar');
    });

    it('não deve desativar o botão se o formulário for inválido no client-side (sem novalidate)', () => {
        form.noValidate = false;
        form.setValidity(false); // Formulário inválido

        const event = {
            target: form,
            defaultPrevented: false,
            preventDefault() {}
        };

        handleSubmit(event);

        // O botão NÃO deve estar desativado porque a validação falhou e o navegador não disparará a requisição
        assert.equal(submitBtn.disabled, false);
        assert.equal(form.dataset.submitting, undefined);
    });

    it('não deve desativar o botão se o envio foi cancelado (defaultPrevented)', () => {
        const event = {
            target: form,
            defaultPrevented: true, // ex: confirm() retornou false
            preventDefault() {}
        };

        handleSubmit(event);

        assert.equal(submitBtn.disabled, false);
        assert.equal(form.dataset.submitting, undefined);
    });

    it('deve bloquear envios múltiplos subsequentes (evitar múltiplos disparos)', () => {
        form.dataset.submitting = 'true';

        let prevented = false;
        let propagationStopped = false;

        const event = {
            target: form,
            defaultPrevented: false,
            preventDefault() {
                prevented = true;
            },
            stopImmediatePropagation() {
                propagationStopped = true;
            }
        };

        handleSubmit(event);

        assert.equal(prevented, true);
        assert.equal(propagationStopped, true);
    });

    it('deve desativar no início de uma requisição Turbo e reativar ao final', () => {
        const startEvent = {
            detail: { formSubmission: { formElement: form } }
        };

        handleTurboSubmitStart(startEvent);
        assert.equal(submitBtn.disabled, true);
        assert.equal(form.dataset.submitting, 'true');

        const endEvent = {
            detail: { formSubmission: { formElement: form }, success: false } // ex: 422 formulário inválido
        };

        handleTurboSubmitEnd(endEvent);
        assert.equal(submitBtn.disabled, false);
        assert.equal(form.dataset.submitting, undefined);
        assert.equal(submitBtn.innerHTML, '<i class="bi bi-check-lg"></i> Salvar');
    });

    it('deve reativar botões se o evento invalid for disparado em um campo', () => {
        disableSubmitButtons(form);
        assert.equal(submitBtn.disabled, true);

        const invalidEvent = {
            target: { form: form }
        };

        handleInvalid(invalidEvent);

        assert.equal(submitBtn.disabled, false);
        assert.equal(form.dataset.submitting, undefined);
    });

    it('deve reativar botões se o usuário digitar em um campo após tentativa de envio', () => {
        disableSubmitButtons(form);
        assert.equal(submitBtn.disabled, true);

        const inputEvent = {
            target: { form: form }
        };

        handleInput(inputEvent);

        assert.equal(submitBtn.disabled, false);
        assert.equal(form.dataset.submitting, undefined);
    });

    it('deve respeitar opt-out quando data-prevent-double-submit="false"', () => {
        form.dataset.preventDoubleSubmit = 'false';
        assert.equal(shouldHandleForm(form), false);
    });

    it('deve respeitar opt-out quando data-disable-on-submit="false"', () => {
        form.dataset.disableOnSubmit = 'false';
        assert.equal(shouldHandleForm(form), false);
    });

    it('deve desativar e reativar múltiplos botões de envio no mesmo formulário', () => {
        const secondBtn = new MockButton({ type: 'submit' });
        secondBtn.innerHTML = 'Salvar e Continuar';
        secondBtn.textContent = 'Salvar e Continuar';
        secondBtn.form = form;
        form._elements = [submitBtn, secondBtn];

        disableSubmitButtons(form);
        assert.equal(submitBtn.disabled, true);
        assert.equal(secondBtn.disabled, true);

        enableSubmitButtons(form);
        assert.equal(submitBtn.disabled, false);
        assert.equal(secondBtn.disabled, false);
    });
});
