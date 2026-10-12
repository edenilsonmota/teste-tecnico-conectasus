-- Consulta 1: pacientes e respectivas unidades de saúde
SELECT
    p.id AS patient_id,
    p.name AS patient_name,
    p.cpf AS patient_cpf,
    hu.name AS health_unit_name
FROM patients AS p
INNER JOIN health_units AS hu ON hu.id = p.health_unit_id
ORDER BY p.name;

-- Consulta 2: quantidade de pacientes por unidade de saúde
SELECT
    hu.id AS health_unit_id,
    hu.name AS health_unit_name,
    COUNT(p.id) AS patient_count
FROM health_units AS hu
LEFT JOIN patients AS p ON p.health_unit_id = hu.id
GROUP BY hu.id, hu.name
ORDER BY hu.name;

-- Consulta 3: busca parcial de pacientes por nome

SELECT
    p.id AS patient_id,
    p.name AS patient_name,
    p.cpf AS patient_cpf,
    p.birth_date AS patient_birth_date,
    p.phone AS patient_phone,
    hu.name AS health_unit_name
FROM patients AS p
INNER JOIN health_units AS hu ON hu.id = p.health_unit_id
WHERE p.name LIKE '%Maria%'
ORDER BY p.name;
