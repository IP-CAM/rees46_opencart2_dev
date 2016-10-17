<?php
class ControllerModuleRees46 extends Controller {
	public function index($setting) {
		$data['module_id'] = $setting['module_id'];
		$data['type'] = $setting['type'];

		if ($setting['css']) {
			$data['css'] = $setting['css'];
		} else {
			$data['css'] = false;
		}

		if (isset($this->request->get['product_id'])) {
			$item = (int)$this->request->get['product_id'];
		}

		if (isset($this->request->get['path'])) {
			$categories = explode('_', (string)$this->request->get['path']);

			$category = (int)array_pop($categories);
		}

		if ($this->cart->hasProducts()) {
			foreach ($this->cart->getProducts() as $product) {
				$cart[] = $product['product_id'];
			}
		}

		if (isset($this->request->get['search'])) {
			$search_query = $this->request->get['search'];;
		}

		$params = array();

		if ($setting['limit'] > 0) {
			$params['limit'] = 6;
		} else {
			$params['limit'] = (int)$setting['limit'];
		}

		$params['discount'] = (int)$setting['discount'];

		if ($data['type'] == 'interesting') {
			if (isset($item)) {
				$params['item'] = $item;
			}

			$data['params'] = json_encode($params, true);
		} elseif ($data['type'] == 'also_bought') {
			if (isset($item)) {
				$params['item'] = $item;

				$data['params'] = json_encode($params, true);
			}
		} elseif ($data['type'] == 'similar') {
			if (isset($item) && isset($cart)) {
				$params['item'] = $item;
				$params['cart'] = $cart;

				if (isset($categories)) {
					$params['categories'] = $categories;
				}

				$data['params'] = json_encode($params, true);
			}
		} elseif ($data['type'] == 'popular') {
			if (isset($category)) {
				$params['category'] = $category;
			}

			$data['params'] = json_encode($params, true);
		} elseif ($data['type'] == 'see_also') {
			if (isset($cart)) {
				$params['cart'] = $cart;

				$data['params'] = json_encode($params, true);
			}
		} elseif ($data['type'] == 'recently_viewed') {
			$data['params'] = json_encode($params, true);
		} elseif ($data['type'] == 'buying_now') {
			if (isset($item)) {
				$params['item'] = $item;
			}

			if (isset($cart)) {
				$params['cart'] = $cart;
			}

			$data['params'] = json_encode($params, true);
		} elseif ($data['type'] == 'search') {
			if (isset($search_query)) {
				$params['search_query'] = $search_query;

				if (isset($cart)) {
					$params['cart'] = $cart;
				}

				$data['params'] = json_encode($params, true);
			}
		}

		if (isset($data['params'])) {
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/rees46.tpl')) {
				return $this->load->view($this->config->get('config_template') . '/template/module/rees46.tpl', $data);
			} else {
				return $this->load->view('default/template/module/rees46.tpl', $data);
			}
		}
	}

	public function getProducts() {
		if (isset($this->request->get['module_id']) && isset($this->request->get['product_ids'])) {
			$this->load->language('module/rees46');

			$this->load->model('extension/module');
			$this->load->model('catalog/product');
			$this->load->model('tool/image');

			$data['text_tax'] = $this->language->get('text_tax');
			$data['text_more'] = $this->language->get('text_more');
			$data['button_cart'] = $this->language->get('button_cart');
			$data['button_wishlist'] = $this->language->get('button_wishlist');
			$data['button_compare'] = $this->language->get('button_compare');

			$setting = $this->model_extension_module->getModule($this->request->get['module_id']);

			if ($setting['title'][$this->config->get('config_language_id')] != '') {
				$data['heading_title'] = html_entity_decode($setting['title'][$this->config->get('config_language_id')], ENT_QUOTES, 'UTF-8');
			} else {
				$data['heading_title'] = $this->language->get('text_type_' . $setting['type']);
			}

			if ($setting['width']) {
				$width = $setting['width'];
			} else {
				$width = 100;
			}

			if ($setting['height']) {
				$height = $setting['height'];
			} else {
				$height = 100;
			}

			$data['products'] = array();

			$product_ids = explode(',', $this->request->get['product_ids']);

			if (!empty($product_ids)) {
				foreach ($product_ids as $product_id) {
					$product_info = $this->model_catalog_product->getProduct($product_id);

					if ($product_info && $product_info['quantity'] > 0) {
						if ($product_info['image']) {
							$image = $this->model_tool_image->resize($product_info['image'], $width, $height);
						} else {
							$image = $this->model_tool_image->resize('placeholder.png', $width, $height);
						}

						if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
							$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')));
						} else {
							$price = false;
						}

						if ((float)$product_info['special']) {
							$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')));
						} else {
							$special = false;
						}

						if ($this->config->get('config_tax')) {
							$tax = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price']);
						} else {
							$tax = false;
						}

						if ($this->config->get('config_review_status')) {
							$rating = $product_info['rating'];
						} else {
							$rating = false;
						}

						$data['products'][] = array(
							'product_id'  => $product_info['product_id'],
							'thumb'       => $image,
							'name'        => $product_info['name'],
							'description' => utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('config_product_description_length')) . '..',
							'price'       => $price,
							'special'     => $special,
							'tax'         => $tax,
							'rating'      => $rating,
							'href'        => $this->url->link('product/product', 'product_id=' . $product_info['product_id'] . '&recommended_by=' . $setting['type'])
						);
					}
				}
			}

			if (!empty($data['products'])) {
				if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/' . $setting['template'] . '.tpl')) {
					$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/module/' . $setting['template'] . '.tpl', $data));
				} else {
					$this->response->setOutput($this->load->view('default/template/module/' . $setting['template'] . '.tpl', $data));
				}
			}
		}
	}
}