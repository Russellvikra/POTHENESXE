INSERT INTO parties (name) VALUES
('DISY'), ('AKEL'), ('DIKO');

INSERT INTO users (username, email, password_hash, role) VALUES
('admin', 'admin@test.com', '$2y$10$kt0jxSzjIohenPym15V7a.4wqF2iaQnnZOmX7sJM17NFQbk.oixBa', 'admin'),
('nikos', 'nikos@test.com', '$2y$10$kt0jxSzjIohenPym15V7a.4wqF2iaQnnZOmX7sJM17NFQbk.oixBa', 'politician'),
('maria', 'maria@test.com', '$2y$10$kt0jxSzjIohenPym15V7a.4wqF2iaQnnX7sJM17NFQbk.oixBa', 'politician');

INSERT INTO politicians (user_id, party_id, position) VALUES
(1, NULL, 'Administrator'),
(2, 1, 'MP'),
(3, 2, 'Minister');

INSERT INTO declarations (user_id, politician_id, title, details, year, status) VALUES
(1, 1, 'Annual Asset Declaration', 'Admin demo declaration record.', 2024, 'submitted'),
(1, 1, 'Annual Asset Update', 'Second admin demo declaration record.', 2025, 'submitted'),
(2, 2, 'Property Disclosure', 'Demo user declaration about property.', 2024, 'submitted'),
(2, 2, 'Income Statement', 'Demo user declaration about income.', 2025, 'draft'),
(3, 3, 'Political Funding Report', 'Demo politician declaration for funding.', 2025, 'submitted');

INSERT INTO assets (declaration_id, type, description, value) VALUES
(1, 'deposit', 'Bank account', 10000),
(1, 'car', 'BMW', 20000),
(3, 'house', 'Limassol apartment', 150000),
(4, 'deposit', 'Savings account', 25000),
(5, 'vehicle', 'Company car', 18000);

INSERT INTO declaration_reviews (declaration_id, reviewer_id, review_note, review_status) VALUES
(1, 1, 'Record reviewed and approved.', 'approved'),
(4, 1, 'Needs better detail in asset descriptions.', 'needs_changes');

INSERT INTO login_audit (user_id, email, login_status, ip_address) VALUES
(1, 'admin@test.com', 'success', '127.0.0.1'),
(2, 'nikos@test.com', 'success', '127.0.0.1'),
(NULL, 'unknown@test.com', 'failed', '127.0.0.1');
