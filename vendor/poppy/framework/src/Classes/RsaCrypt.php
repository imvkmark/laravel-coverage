<?php

namespace Poppy\Framework\Classes;

use Poppy\Framework\Classes\Traits\AppTrait;

/**
 * Rsa 加密/解密
 */
class RsaCrypt
{
	use AppTrait;

	/**
	 * @var string 私钥字串
	 */
	private static $privateKey = '-----BEGIN RSA PRIVATE KEY-----';

	/**
	 * @var string 公钥字串
	 */
	private static $publicKey = '-----BEGIN PUBLIC KEY-----';

	/**
	 * 设置私有 key
	 * @param string $private_key 私钥
	 */
	public function setPrivateKey($private_key)
	{
		self::$privateKey = $private_key;
	}

	/**
	 * 设置公有key
	 * @param string $public_key 公钥
	 */
	public function setPublicKey($public_key)
	{
		self::$publicKey = $public_key;
	}

	/**
	 * 使用私钥进行签名
	 * Rsa2 进行签名
	 * @param  string $data 待签名的数据
	 * @return string
	 */
	public function sign($data): string
	{
		$priKey = str_replace([
			'-----BEGIN RSA PRIVATE KEY-----',
			'-----END RSA PRIVATE KEY-----',
			"\n",
		], '', self::$privateKey);

		$res = "-----BEGIN RSA PRIVATE KEY-----\n" .
			wordwrap($priKey, 64, "\n", true) .
			"\n-----END RSA PRIVATE KEY-----";

		openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);

		return base64_encode($sign);
	}

	/**
	 * 对签名进行验证
	 * @param string $data 待验证的数据
	 * @param string $sign 签名
	 * @return bool
	 */
	public function verify($data = '', $sign = ''): bool
	{
		$publicKey = str_replace([
			'-----BEGIN PUBLIC KEY-----',
			'-----END PUBLIC KEY-----',
			"\n",
		], '', self::$publicKey);

		$res = "-----BEGIN PUBLIC KEY-----\n" .
			wordwrap($publicKey, 64, "\n", true) .
			"\n-----END PUBLIC KEY-----";

		$sign = base64_decode($sign);

		return openssl_verify($data, $sign, $res, OPENSSL_ALGO_SHA256) > 0;
	}

	/**
	 * 私钥加密
	 * @param string $data 待加密的数据
	 * @return null|string
	 */
	public function privateEncrypt($data = '')
	{
		if (!is_string($data)) {
			return null;
		}

		return openssl_private_encrypt($data, $encrypted, self::getPrivateKey()) ? base64_encode($encrypted) : null;
	}

	/**
	 * 公钥加密
	 * @param string $data 待加密的数据
	 * @return null|string
	 */
	public function publicEncrypt($data = '')
	{
		if (!is_string($data)) {
			return null;
		}

		return openssl_public_encrypt($data, $encrypted, self::getPublicKey(), OPENSSL_PKCS1_OAEP_PADDING) ? base64_encode($encrypted) : null;
	}

	/**
	 * 私钥解密
	 * @param string $encrypted 待解密的数据
	 * @return null
	 */
	public function privateDecrypt($encrypted = '')
	{
		if (!is_string($encrypted)) {
			return null;
		}

		return openssl_private_decrypt(base64_decode($encrypted), $decrypted, self::getPrivateKey(), OPENSSL_PKCS1_OAEP_PADDING) ? $decrypted : null;
	}

	/**
	 * 公钥解密
	 * @param string $encrypted 待解密的数据
	 * @return null
	 */
	public function publicDecrypt($encrypted = '')
	{
		if (!is_string($encrypted)) {
			return null;
		}

		return openssl_public_decrypt(base64_decode($encrypted), $decrypted, self::getPublicKey()) ? $decrypted : null;
	}

	/**
	 * 获取私钥
	 * @return bool|resource
	 */
	private static function getPrivateKey()
	{
		return openssl_pkey_get_private(self::$privateKey);
	}

	/**
	 * 获取公钥
	 * @return bool|resource
	 */
	private static function getPublicKey()
	{
		return openssl_pkey_get_public(self::$publicKey);
	}
}