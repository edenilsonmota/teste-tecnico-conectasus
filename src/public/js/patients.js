(() => {
    'use strict';

    // Mantém o estado necessário para busca, paginação e edição na mesma página.
    const state = {
        currentPage: 1,
        lastPage: 1,
        search: '',
        editingPatientId: null,
        patients: new Map(),
        visiblePatients: 0,
    };

    // Centraliza os elementos da interface utilizados pelos eventos e renderizações.
    const elements = {
        messageArea: document.querySelector('#message-area'),
        patientForm: document.querySelector('#patient-form'),
        formTitle: document.querySelector('#form-title'),
        submitButton: document.querySelector('#submit-button'),
        cancelEditButton: document.querySelector('#cancel-edit-button'),
        name: document.querySelector('#name'),
        cpf: document.querySelector('#cpf'),
        birthDate: document.querySelector('#birth-date'),
        phone: document.querySelector('#phone'),
        healthUnit: document.querySelector('#health-unit'),
        searchForm: document.querySelector('#search-form'),
        search: document.querySelector('#search'),
        clearSearchButton: document.querySelector('#clear-search-button'),
        tableBody: document.querySelector('#patients-table-body'),
        previousPageButton: document.querySelector('#previous-page-button'),
        nextPageButton: document.querySelector('#next-page-button'),
        paginationStatus: document.querySelector('#pagination-status'),
    };

    const onlyDigits = (value) => value.replace(/\D/g, '');

    const formatCpf = (value) => {
        const digits = onlyDigits(value).slice(0, 11);

        return digits
            .replace(/^(\d{3})(\d)/, '$1.$2')
            .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
            .replace(/\.(\d{3})(\d)/, '.$1-$2');
    };

    const formatPhone = (value) => {
        const digits = onlyDigits(value).slice(0, 11);

        return digits
            .replace(/^(\d{2})(\d)/, '($1) $2')
            .replace(/(\d{5})(\d)/, '$1-$2');
    };

    // Exibe a data digitada no padrão brasileiro sem alterar a validação da API.
    const formatBrazilianDate = (value) => {
        const digits = onlyDigits(value).slice(0, 8);

        return digits
            .replace(/^(\d{2})(\d)/, '$1/$2')
            .replace(/^(\d{2})\/(\d{2})(\d)/, '$1/$2/$3');
    };

    const toIsoDate = (date) => [
        date.getFullYear(),
        String(date.getMonth() + 1).padStart(2, '0'),
        String(date.getDate()).padStart(2, '0'),
    ].join('-');

    // Confere se uma data brasileira existe antes de convertê-la para a API.
    const parseBrazilianDate = (value) => {
        const match = value.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);

        if (!match) {
            return null;
        }

        const [, day, month, year] = match;
        const date = new Date(Number(year), Number(month) - 1, Number(day));

        return date.getFullYear() === Number(year)
            && date.getMonth() === Number(month) - 1
            && date.getDate() === Number(day)
            ? date
            : null;
    };

    const formatDateForApi = (value) => {
        const date = parseBrazilianDate(value);

        return date ? toIsoDate(date) : value;
    };

    const formatDateForInput = (value) => {
        const match = value?.match(/^(\d{4})-(\d{2})-(\d{2})/);

        return match ? `${match[3]}/${match[2]}/${match[1]}` : '';
    };

    // Mostra retornos de sucesso, erros gerais e mensagens de validação na tela.
    const showMessage = (type, messages) => {
        const messageList = Array.isArray(messages) ? messages : [messages];

        elements.messageArea.replaceChildren();
        elements.messageArea.className = `message-area message-${type}`;
        elements.messageArea.setAttribute('role', type === 'error' ? 'alert' : 'status');

        if (messageList.length === 1) {
            elements.messageArea.textContent = messageList[0];
        } else {
            const list = document.createElement('ul');

            messageList.forEach((message) => {
                const item = document.createElement('li');
                item.textContent = message;
                list.append(item);
            });

            elements.messageArea.append(list);
        }

        elements.messageArea.hidden = false;
        elements.messageArea.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    };

    const getErrorMessages = (error) => {
        if (error.status === 422 && error.payload?.errors) {
            return Object.values(error.payload.errors).flat();
        }

        if (error.status === 404) {
            return ['O registro solicitado não foi encontrado.'];
        }

        return ['Não foi possível concluir a operação. Tente novamente.'];
    };

    // Padroniza as requisições JSON e transforma respostas de erro em exceções tratáveis.
    const apiRequest = async (url, options = {}) => {
        let response;

        try {
            response = await fetch(url, {
                ...options,
                headers: {
                    Accept: 'application/json',
                    ...options.headers,
                },
            });
        } catch (networkError) {
            const error = new Error('Falha de rede.');
            error.cause = networkError;
            throw error;
        }

        if (response.status === 204) {
            return null;
        }

        const payload = await response.json().catch(() => null);

        if (!response.ok) {
            const error = new Error(payload?.message ?? 'Erro retornado pela API.');
            error.status = response.status;
            error.payload = payload;
            throw error;
        }

        return payload;
    };

    const createTextCell = (value) => {
        const cell = document.createElement('td');
        cell.textContent = value;

        return cell;
    };

    // Atualiza a tabela com segurança, usando textContent para os dados da API.
    const renderPatients = (patients) => {
        elements.tableBody.replaceChildren();
        state.patients.clear();
        state.visiblePatients = patients.length;

        if (patients.length === 0) {
            const row = document.createElement('tr');
            const cell = createTextCell('Nenhum paciente encontrado.');
            cell.colSpan = 4;
            cell.className = 'empty-state';
            row.append(cell);
            elements.tableBody.append(row);

            return;
        }

        patients.forEach((patient) => {
            state.patients.set(Number(patient.id), patient);

            const row = document.createElement('tr');
            const actionsCell = document.createElement('td');
            const actions = document.createElement('div');
            const editButton = document.createElement('button');
            const deleteButton = document.createElement('button');

            actions.className = 'row-actions';
            editButton.type = 'button';
            editButton.className = 'button button-edit';
            editButton.dataset.action = 'edit';
            editButton.dataset.patientId = patient.id;
            editButton.textContent = 'Editar';
            deleteButton.type = 'button';
            deleteButton.className = 'button button-danger';
            deleteButton.dataset.action = 'delete';
            deleteButton.dataset.patientId = patient.id;
            deleteButton.textContent = 'Excluir';

            actions.append(editButton, deleteButton);
            actionsCell.append(actions);
            row.append(
                createTextCell(patient.name ?? ''),
                createTextCell(formatCpf(patient.cpf ?? '')),
                createTextCell(patient.health_unit?.name ?? 'Unidade não informada'),
                actionsCell,
            );
            elements.tableBody.append(row);
        });
    };

    const updatePagination = () => {
        elements.paginationStatus.textContent = `Página ${state.currentPage} de ${state.lastPage}`;
        elements.previousPageButton.disabled = state.currentPage <= 1;
        elements.nextPageButton.disabled = state.currentPage >= state.lastPage;
    };

    // Carrega a página solicitada e preserva o termo de busca atual.
    const loadPatients = async (page = 1) => {
        const parameters = new URLSearchParams({
            search: state.search,
            page: String(page),
        });

        try {
            const pagination = await apiRequest(`/api/patients?${parameters.toString()}`);

            state.currentPage = pagination.current_page;
            state.lastPage = Math.max(pagination.last_page, 1);
            renderPatients(pagination.data);
            updatePagination();
        } catch (error) {
            renderPatients([]);
            updatePagination();
            showMessage('error', 'Não foi possível carregar os pacientes. Tente novamente.');
        }
    };

    // Busca as unidades para preencher o select do formulário.
    const loadHealthUnits = async () => {
        try {
            const healthUnits = await apiRequest('/api/health-units');
            const placeholder = document.createElement('option');

            placeholder.value = '';
            placeholder.textContent = 'Selecione uma unidade';
            elements.healthUnit.replaceChildren(placeholder);

            healthUnits.forEach((healthUnit) => {
                const option = document.createElement('option');
                option.value = String(healthUnit.id);
                option.textContent = `${healthUnit.name} — ${healthUnit.city}`;
                elements.healthUnit.append(option);
            });
        } catch (error) {
            showMessage('error', 'Não foi possível carregar as unidades de saúde. Tente novamente.');
        }
    };

    // Retorna o formulário ao modo de cadastro após cancelar ou concluir uma edição.
    const resetForm = () => {
        state.editingPatientId = null;
        elements.patientForm.reset();
        elements.formTitle.textContent = 'Cadastrar paciente';
        elements.submitButton.textContent = 'Cadastrar paciente';
        elements.cancelEditButton.hidden = true;
    };

    // Busca os dados atuais do paciente e preenche o formulário para edição.
    const startEditing = async (patientId) => {
        try {
            const patient = await apiRequest(`/api/patients/${patientId}`);

            state.editingPatientId = Number(patient.id);
            elements.name.value = patient.name ?? '';
            elements.cpf.value = formatCpf(patient.cpf ?? '');
            elements.birthDate.value = formatDateForInput(patient.birth_date);
            elements.phone.value = formatPhone(patient.phone ?? '');
            elements.healthUnit.value = String(patient.health_unit_id ?? '');
            elements.formTitle.textContent = 'Editar paciente';
            elements.submitButton.textContent = 'Salvar alterações';
            elements.cancelEditButton.hidden = false;
            elements.patientForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
            elements.name.focus({ preventScroll: true });
        } catch (error) {
            showMessage('error', getErrorMessages(error));
        }
    };

    // Confirma a exclusão e volta uma página quando o último registro é removido.
    const deletePatient = async (patientId) => {
        const patient = state.patients.get(patientId);
        const patientName = patient?.name ? ` “${patient.name}”` : '';

        if (!window.confirm(`Deseja realmente excluir o paciente${patientName}?`)) {
            return;
        }

        try {
            await apiRequest(`/api/patients/${patientId}`, { method: 'DELETE' });

            if (state.editingPatientId === patientId) {
                resetForm();
            }

            const targetPage = state.visiblePatients === 1 && state.currentPage > 1
                ? state.currentPage - 1
                : state.currentPage;

            await loadPatients(targetPage);
            showMessage('success', 'Paciente excluído com sucesso.');
        } catch (error) {
            showMessage('error', getErrorMessages(error));
        }
    };

    elements.cpf.addEventListener('input', (event) => {
        event.target.value = formatCpf(event.target.value);
    });

    elements.phone.addEventListener('input', (event) => {
        event.target.value = formatPhone(event.target.value);
    });

    elements.birthDate.addEventListener('input', (event) => {
        event.target.value = formatBrazilianDate(event.target.value);
    });

    // Remove as máscaras visuais e envia o mesmo formulário para criar ou atualizar.
    elements.patientForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const payload = {
            name: elements.name.value.trim(),
            cpf: onlyDigits(elements.cpf.value),
            birth_date: formatDateForApi(elements.birthDate.value),
            phone: onlyDigits(elements.phone.value) || null,
            health_unit_id: elements.healthUnit.value === ''
                ? null
                : Number(elements.healthUnit.value),
        };
        const isEditing = state.editingPatientId !== null;
        const endpoint = isEditing
            ? `/api/patients/${state.editingPatientId}`
            : '/api/patients';

        elements.submitButton.disabled = true;

        try {
            await apiRequest(endpoint, {
                method: isEditing ? 'PATCH' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            resetForm();
            await loadPatients(isEditing ? state.currentPage : 1);
            showMessage('success', isEditing
                ? 'Paciente atualizado com sucesso.'
                : 'Paciente cadastrado com sucesso.');
        } catch (error) {
            showMessage('error', getErrorMessages(error));
        } finally {
            elements.submitButton.disabled = false;
        }
    });

    elements.cancelEditButton.addEventListener('click', resetForm);

    elements.searchForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        state.search = elements.search.value.trim();
        await loadPatients(1);
    });

    elements.clearSearchButton.addEventListener('click', async () => {
        elements.search.value = '';
        state.search = '';
        await loadPatients(1);
    });

    elements.previousPageButton.addEventListener('click', () => {
        if (state.currentPage > 1) {
            loadPatients(state.currentPage - 1);
        }
    });

    elements.nextPageButton.addEventListener('click', () => {
        if (state.currentPage < state.lastPage) {
            loadPatients(state.currentPage + 1);
        }
    });

    elements.tableBody.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-action]');

        if (!button) {
            return;
        }

        const patientId = Number(button.dataset.patientId);

        if (button.dataset.action === 'edit') {
            startEditing(patientId);
        }

        if (button.dataset.action === 'delete') {
            deletePatient(patientId);
        }
    });

    Promise.allSettled([
        loadHealthUnits(),
        loadPatients(),
    ]);
})();
