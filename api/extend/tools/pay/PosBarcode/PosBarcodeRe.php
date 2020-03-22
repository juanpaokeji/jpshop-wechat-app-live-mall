<?php

namespace tools\pay\pos_barcode;

/**
 * 扫码返回
 *
 */
class PosBarcodeRe {
	
	private $return_code;//响应码
	
	private $return_msg;//返回信息提示
		
	private $result_code;//业务结果
	
	private $pay_type;//请求类型
	
	private $merchant_name;//商户名称
	
	private $merchant_no;//商户号
	
	private $terminal_id;//终端号
	
	private $terminal_trace;//终端流水号
	
	private $terminal_time;//终端交易时间
	
	private $total_fee;//金额
	
	private $end_time;//支付完成时间
	
	private $out_trade_no;//唯一订单号
	
	private $key_sign;//签名检验串

	public function getReturn_code() {
		return $this->return_code;
	}

	public function setReturn_code($return_code) {
		$this->return_code = $return_code;
	}

	public function getReturn_msg() {
		return $this->return_msg;
	}

	public function setReturn_msg($return_msg) {
		$this->return_msg = $return_msg;
	}

	public function getResult_code() {
		return $this->result_code;
	}

	public function setResult_code($result_code) {
		$this->result_code = $result_code;
	}

	public function getPay_type() {
		return $this->pay_type;
	}

	public function setPay_type($pay_type) {
		$this->pay_type = $pay_type;
	}

	public function getMerchant_name() {
		return $this->merchant_name;
	}

	public function setMerchant_name($merchant_name) {
		$this->merchant_name = $merchant_name;
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

	public function getEnd_time() {
		return $this->end_time;
	}

	public function setEnd_time($end_time) {
		$this->end_time = $end_time;
	}

	public function getOut_trade_no() {
		return $this->out_trade_no;
	}

	public function setOut_trade_no($out_trade_no) {
		$this->out_trade_no = $out_trade_no;
	}

	public function getKey_sign() {
		return $this->key_sign;
	}

	public function setKey_sign($key_sign) {
		$this->key_sign = $key_sign;
	}

	

}
