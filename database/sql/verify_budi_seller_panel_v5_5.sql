SELECT 'products' AS module, COUNT(*) AS total FROM products p JOIN stores s ON s.id = p.store_id JOIN users u ON u.id = s.user_id WHERE u.email = 'budi@gmail.com' AND p.slug LIKE 'budi-test-product-%'
UNION ALL
SELECT 'orders', COUNT(*) FROM orders WHERE order_number LIKE 'BUDI-TEST-%'
UNION ALL
SELECT 'customers', COUNT(DISTINCT o.user_id) FROM orders o JOIN sub_orders so ON so.order_id = o.id JOIN stores s ON s.id = so.store_id JOIN users u ON u.id = s.user_id WHERE u.email = 'budi@gmail.com' AND o.order_number LIKE 'BUDI-TEST-%' AND o.status <> 'cancelled'
UNION ALL
SELECT 'reviews', COUNT(*) FROM product_reviews pr JOIN products p ON p.id = pr.product_id JOIN stores s ON s.id = p.store_id JOIN users u ON u.id = s.user_id WHERE u.email = 'budi@gmail.com' AND p.slug LIKE 'budi-test-product-%'
UNION ALL
SELECT 'vouchers', COUNT(*) FROM vouchers v JOIN stores s ON s.id = v.store_id JOIN users u ON u.id = s.user_id WHERE u.email = 'budi@gmail.com' AND v.code LIKE 'BUDITEST%'
UNION ALL
SELECT 'promotions', COUNT(*) FROM promotions p JOIN stores s ON s.id = p.store_id JOIN users u ON u.id = s.user_id WHERE u.email = 'budi@gmail.com' AND p.name LIKE 'Budi Test Promotion %'
UNION ALL
SELECT 'showcases', COUNT(*) FROM showcases sh JOIN stores s ON s.id = sh.store_id JOIN users u ON u.id = s.user_id WHERE u.email = 'budi@gmail.com' AND sh.slug LIKE 'budi-test-showcase-%'
UNION ALL
SELECT 'finance', COUNT(*) FROM financial_transactions f JOIN stores s ON s.id = f.store_id JOIN users u ON u.id = s.user_id WHERE u.email = 'budi@gmail.com' AND f.reference_number LIKE 'BUDI-TEST-FIN-%'
UNION ALL
SELECT 'raw_materials', COUNT(*) FROM raw_materials rm JOIN stores s ON s.id = rm.store_id JOIN users u ON u.id = s.user_id WHERE u.email = 'budi@gmail.com' AND rm.code LIKE 'BUDI-RM-%'
UNION ALL
SELECT 'conversations', COUNT(*) FROM conversations c JOIN stores s ON s.id = c.store_id JOIN users u ON u.id = s.user_id WHERE u.email = 'budi@gmail.com' AND c.subject LIKE 'BUDI-TEST-CHAT-%'
UNION ALL
SELECT 'tickets', COUNT(*) FROM support_tickets t JOIN stores s ON s.id = t.store_id JOIN users u ON u.id = s.user_id WHERE u.email = 'budi@gmail.com' AND t.ticket_number LIKE 'BUDI-TEST-TICKET-%'
UNION ALL
SELECT 'raw_material_cost_histories', COUNT(*) FROM raw_material_cost_histories h JOIN stores s ON s.id = h.store_id JOIN users u ON u.id = s.user_id WHERE u.email = 'budi@gmail.com' AND h.reference_number LIKE 'BUDI-COST-%'
UNION ALL
SELECT 'product_costing_impacts', COUNT(*) FROM product_costing_impacts i JOIN stores s ON s.id = i.store_id JOIN users u ON u.id = s.user_id WHERE u.email = 'budi@gmail.com';
