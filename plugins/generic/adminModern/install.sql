-- Register and enable the adminModern generic plugin.
-- Run once per environment (these rows live in the DB, not in git).
-- Enabled per-press (context_id 1 = the `arado` press). NB: plugin_settings.
-- context_id has a FK to presses(press_id), so context_id 0 is NOT valid here.

INSERT INTO versions
  (major, minor, revision, build, date_installed, current, product_type, product, product_class_name, lazy_load, sitewide)
SELECT 1, 0, 0, 0, NOW(), 1, 'plugins.generic', 'adminModern', 'AdminModernPlugin', 1, 0
WHERE NOT EXISTS (SELECT 1 FROM versions WHERE product = 'adminModern' AND current = 1);

INSERT INTO plugin_settings (plugin_name, context_id, setting_name, setting_value, setting_type)
VALUES ('adminmodernplugin', 1, 'enabled', '1', 'bool')
ON DUPLICATE KEY UPDATE setting_value = '1';
