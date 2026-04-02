INSERT INTO parties (name) VALUES 
('DISY'), ('AKEL'), ('DIKO');

INSERT INTO users (username, first_name, last_name, phone, email, password_hash, role) VALUES
('admin', 'System', 'Admin', '99000000', 'admin@test.com', '$2y$10$kt0jxSzjIohenPym15V7a.4wqF2iaQnnZOmX7sJM17NFQbk.oixBa', 'admin'),
('nikos', 'Nikos', 'Andreou', '99111111', 'nikos@test.com', '$2y$10$kt0jxSzjIohenPym15V7a.4wqF2iaQnnZOmX7sJM17NFQbk.oixBa', 'politician'),
('maria', 'Maria', 'Savva', '99222222', 'maria@test.com', '$2y$10$kt0jxSzjIohenPym15V7a.4wqF2iaQnnZOmX7sJM17NFQbk.oixBa', 'politician');

INSERT INTO politicians (user_id, party_id, position) VALUES
(2, 1, 'MP'),
(3, 2, 'Minister');

INSERT INTO declarations (politician_id, year, status) VALUES
(1, 2024, 'submitted'),
(2, 2024, 'submitted');

INSERT INTO assets (declaration_id, type, description, value) VALUES
(1, 'deposit', 'Bank account', 10000),
(1, 'car', 'BMW', 20000),
(2, 'house', 'Limassol', 150000);