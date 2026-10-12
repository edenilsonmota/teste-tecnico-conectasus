<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Cadastro de Pacientes</title>

        <link rel="stylesheet" href="{{ asset('css/patients.css') }}">
        <script src="{{ asset('js/patients.js') }}" defer></script>
    </head>
    <body>
        <main class="page-shell">
            <header class="page-header">
                <h1>Cadastro de Pacientes</h1>
                <p>Gerencie pacientes e mantenha cada cadastro vinculado à sua unidade de saúde.</p>
            </header>

            <div id="message-area" class="message-area" role="status" aria-live="polite" hidden></div>

            <section class="card" aria-labelledby="form-title">
                <div class="section-heading">
                    <div>
                        <p class="section-kicker">Dados do paciente</p>
                        <h2 id="form-title">Cadastrar paciente</h2>
                    </div>
                </div>

                <form id="patient-form" novalidate>
                    <div class="form-grid">
                        <div class="field field-wide">
                            <label for="name">Nome</label>
                            <input id="name" name="name" type="text" autocomplete="name" placeholder="Nome completo">
                        </div>

                        <div class="field">
                            <label for="cpf">CPF</label>
                            <input id="cpf" name="cpf" type="text" inputmode="numeric" autocomplete="off" placeholder="000.000.000-00">
                        </div>

                        <div class="field">
                            <label for="birth-date">Data de nascimento</label>
                            <input id="birth-date" name="birth_date" type="text" inputmode="numeric" autocomplete="bday" placeholder="dd/mm/aaaa" maxlength="10">
                        </div>

                        <div class="field">
                            <label for="phone">Telefone</label>
                            <input id="phone" name="phone" type="text" inputmode="tel" autocomplete="tel" placeholder="(00) 00000-0000">
                        </div>

                        <div class="field field-wide">
                            <label for="health-unit">Unidade de saúde</label>
                            <select id="health-unit" name="health_unit_id">
                                <option value="">Selecione uma unidade</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button id="submit-button" class="button button-primary" type="submit">Cadastrar paciente</button>
                        <button id="cancel-edit-button" class="button button-secondary" type="button" hidden>Cancelar edição</button>
                    </div>
                </form>
            </section>

            <section class="card" aria-labelledby="patients-title">
                <div class="section-heading section-heading-list">
                    <div>
                        <p class="section-kicker">Registros</p>
                        <h2 id="patients-title">Pacientes cadastrados</h2>
                    </div>

                    <form id="search-form" class="search-form" role="search">
                        <label class="sr-only" for="search">Buscar paciente por nome</label>
                        <input id="search" name="search" type="search" placeholder="Buscar por nome">
                        <button class="button button-primary" type="submit">Buscar</button>
                        <button id="clear-search-button" class="button button-secondary" type="button">Limpar</button>
                    </form>
                </div>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th scope="col">Nome</th>
                                <th scope="col">CPF</th>
                                <th scope="col">Unidade de saúde</th>
                                <th scope="col" class="actions-column">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="patients-table-body">
                            <tr>
                                <td class="empty-state" colspan="4">Carregando pacientes...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <nav class="pagination" aria-label="Paginação dos pacientes">
                    <button id="previous-page-button" class="button button-secondary" type="button">Anterior</button>
                    <span id="pagination-status">Página 1 de 1</span>
                    <button id="next-page-button" class="button button-secondary" type="button">Próxima</button>
                </nav>
            </section>
        </main>
    </body>
</html>
