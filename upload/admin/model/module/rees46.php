<?php
class ModelModuleRees46 extends Model {
	public function getOrder($order_id) {
		$query = $this->db->query("SELECT order_id, customer_id, email, order_status_id, date_added FROM `" . DB_PREFIX . "order`");

		if ($query->num_rows) {
			return $query->row;
		} else {
			return;
		}
	}

	public function getOrders($data = array()) {
		$sql = "SELECT order_id, customer_id, email, order_status_id, date_added FROM `" . DB_PREFIX . "order` WHERE DATE(date_added) > DATE_SUB(NOW(), INTERVAL 6 MONTH)";

		$rees46_statuses = array();

		if ($this->config->get('rees46_status_created')) {
			$rees46_statuses = array_merge($rees46_statuses, $this->config->get('rees46_status_created'));
		}

		if ($this->config->get('rees46_status_completed')) {
			$rees46_statuses = array_merge($rees46_statuses, $this->config->get('rees46_status_completed'));
		}

		if ($this->config->get('rees46_status_cancelled')) {
			$rees46_statuses = array_merge($rees46_statuses, $this->config->get('rees46_status_cancelled'));
		}

		if (!empty($rees46_statuses)) {
			$implode = array();

			foreach ($rees46_statuses as $order_status_id) {
				$implode[] = "order_status_id = '" . (int)$order_status_id . "'";
			}

			if ($implode) {
				$sql .= " AND (" . implode(" OR ", $implode) . ")";
			}

			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}

				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}

				$sql .= " ORDER BY order_id ASC LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}

			$query = $this->db->query($sql);

			return $query->rows;
		}
	}

	public function getTotalOrders() {
		$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE DATE(date_added) > DATE_SUB(NOW(), INTERVAL 6 MONTH)";

		$rees46_statuses = array();

		if ($this->config->get('rees46_status_created')) {
			$rees46_statuses = array_merge($rees46_statuses, $this->config->get('rees46_status_created'));
		}

		if ($this->config->get('rees46_status_completed')) {
			$rees46_statuses = array_merge($rees46_statuses, $this->config->get('rees46_status_completed'));
		}

		if ($this->config->get('rees46_status_cancelled')) {
			$rees46_statuses = array_merge($rees46_statuses, $this->config->get('rees46_status_cancelled'));
		}

		if (!empty($rees46_statuses)) {
			$implode = array();

			foreach ($rees46_statuses as $order_status_id) {
				$implode[] = "order_status_id = '" . (int)$order_status_id . "'";
			}

			if ($implode) {
				$sql .= " AND (" . implode(" OR ", $implode) . ")";
			}

			$sql .= " ORDER BY order_id ASC";

			$query = $this->db->query($sql);

			return $query->row['total'];
		}
	}

	public function getOrderProducts($order_id) {
		$query = $this->db->query("SELECT op.product_id, op.price, op.quantity, p.quantity AS stock FROM " . DB_PREFIX . "order_product op LEFT JOIN " . DB_PREFIX . "product p ON (op.product_id = p.product_id) WHERE op.order_id = '" . (int)$order_id . "'");

		return $query->rows;
	}

	public function getAllCategories() {
		$query = $this->db->query("SELECT c.category_id, c.parent_id, cd.name FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1' ORDER BY c.sort_order, LCASE(cd.name)");

		return $query->rows;
	}
}