<?php
class ControllerCommonRees46 extends Controller {
	public function index() {
		if ($this->config->get('rees46_tracking_status')) {
			$data['shop_id'] = $this->config->get('rees46_shop_id');

			if (isset($this->request->get['route'])) {
				$data['route'] = $this->request->get['route'];

				if ($this->request->get['route'] == 'product/product' && isset($this->request->get['product_id'])) {
					$data['product_id'] = $this->request->get['product_id'];
				}

				if ($this->customer->isLogged()) {
					$data['customer_id'] = $this->customer->getId();
					$data['customer_email'] = $this->customer->getEmail();
				} elseif (isset($this->session->data['guest']['email'])) {
					$data['guest_email'] = $this->session->data['guest']['email'];
				}
			} else {
				$data['route'] = 'common/home';
			}

			$cart = array();

			$products = $this->cart->getProducts();

			if ($products) {
				foreach ($products as $product) {
					$cart[] = array(
						'id'     => $product['product_id'],
						'amount' => $product['quantity']
					);
				}
			}

			$data['cart'] = json_encode($cart);

			return $this->load->view('common/rees46', $data);
		}
	}

	public function getCartProducts() {
		$json = array();

		$products = $this->cart->getProducts();

		if ($products) {
			foreach ($products as $product) {
				$json[] = array(
					'id'     => $product['product_id'],
					'amount' => $product['quantity']
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}