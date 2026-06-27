-- Register and enable the bookbotSso generic plugin.
-- Run once per environment (these rows live in the DB, not in git).
-- Assumes the press context_id is 1 (the `arado` press).

INSERT INTO versions
  (major, minor, revision, build, date_installed, current, product_type, product, product_class_name, lazy_load, sitewide)
SELECT 1, 0, 0, 0, NOW(), 1, 'plugins.generic', 'bookbotSso', 'BookbotSsoPlugin', 1, 0
WHERE NOT EXISTS (SELECT 1 FROM versions WHERE product = 'bookbotSso' AND current = 1);

INSERT INTO plugin_settings (plugin_name, context_id, setting_name, setting_value, setting_type)
VALUES ('bookbotssoplugin', 1, 'enabled', '1', 'bool')
ON DUPLICATE KEY UPDATE setting_value = '1';
