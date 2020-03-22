<?php

namespace tools\pay\pos_barcode;

/**
 * 扫码支付实体
 * @author liuchu-zy
 *
 */
class PosBarcode {
	
	private $pay_ver;//版本号
	
	private $pay_type;//请求类型
	
	private $service_id;//接口类型
	
	private $merchant_no;//商户号
	
	private $terminal_id;//终端号
	
	private $terminal_trace;//终端流水号
	
	private $terminal_time;//终端交易时间
	
	private $total_fee;//金额
	
	private $auth_no;//授权码
	
	private $key_sign; //签名检验串

	

	public function getPay_ver() {
		return $this->pay_ver;
	}

	public function setPay_ver($pay_ver) {
		$this->pay_ver = $pay_ver;
	}

	public function getPay_type() {
		return $this->pay_type;
	}

	public function setPay_type($pay_type) {
		$this->pay_type = $pay_type;
	}

	public function getService_id() {
		return $this->service_id;
	}

	public function setService_id($service_id) {
		$this->service_id = $service_id;
	}

	public function getMerchant_no() {
		return $this->merchant_no;
	}

	public function setMerchant_no($merchant_no) {
		$this->merchant_no = $merchant_no;
	}

	public function getTerminal_id() {
		return $this->terminal_id;
	}

	public function setTerminal_id($terminal_id) {
		$this->terminal_id = $terminal_id;
	}

	public function getTerminal_trace() {
		return $this->terminal_trace;
	}

	public function setTerminal_trace($terminal_trace) {
		$this->terminal_trace = $terminal_trace;
	}

	public function getTerminal_time() {
		return $this->terminal_time;
	}

	public function setTerminal_time($terminal_time) {
		$this->terminal_time = $terminal_time;
	}

	public function getTotal_fee() {
		return $this->total_fee;
	}

	public function setTotal_fee($total_fee) {
		$this->total_fee = $total_fee;
	}

	public function getAuth_no() {
		return $this->auth_no;
	}

	public function setAuth_no($auth_no) {
		$this->auth_no = $auth_no;
	}


	public function getKey_sign() {
		return $this->key_sign;
	}

	public function setKey_sign($key_sign) {
		$this->key_sign = $key_sign;
	}


	

}
