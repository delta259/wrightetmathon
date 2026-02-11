<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter Encryption Class
 *
 * Provides two-way keyed encoding using XOR Hashing and OpenSSL
 * (PHP 8 compatibility: mcrypt replaced with openssl)
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/encryption.html
 */
class CI_Encrypt {

	var $CI;
	var $encryption_key	= '';
	var $_hash_type	= 'sha1';
	var $_mcrypt_exists = FALSE;
	var $_mcrypt_cipher;
	var $_mcrypt_mode;

	// OpenSSL cipher and mode
	var $_openssl_cipher = 'aes-256-cbc';

	/**
	 * Constructor
	 *
	 * Simply determines whether the openssl library exists.
	 *
	 */
	public function __construct()
	{
		$this->CI =& get_instance();
		$this->_mcrypt_exists = function_exists('openssl_encrypt');
		log_message('debug', "Encrypt Class Initialized");
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch the encryption key
	 *
	 * Returns it as MD5 in order to have an exact-length 128 bit key.
	 * Mcrypt is sensitive to keys that are not the correct length
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function get_key($key = '')
	{
		if ($key == '')
		{
			if ($this->encryption_key != '')
			{
				return $this->encryption_key;
			}

			$CI =& get_instance();
			$key = $CI->config->item('encryption_key');

			if ($key == FALSE)
			{
				show_error('In order to use the encryption class requires that you set an encryption key in your config file.');
			}
		}

		return md5($key);
	}

	// --------------------------------------------------------------------

	/**
	 * Set the encryption key
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	function set_key($key = '')
	{
		$this->encryption_key = $key;
	}

	// --------------------------------------------------------------------

	/**
	 * Encode
	 *
	 * Encodes the message string using bitwise XOR encoding.
	 * The key is combined with a random hash, and then it
	 * too gets converted using XOR. The whole thing is then run
	 * through openssl (if supported) using the randomized key.
	 * The end result is a double-encrypted message string
	 * that is randomized with each call to this function,
	 * even if the supplied message and key are the same.
	 *
	 * @access	public
	 * @param	string	the string to encode
	 * @param	string	the key
	 * @return	string
	 */
	function encode($string, $key = '')
	{
		$key = $this->get_key($key);

		if ($this->_mcrypt_exists === TRUE)
		{
			$enc = $this->mcrypt_encode($string, $key);
		}
		else
		{
			$enc = $this->_xor_encode($string, $key);
		}

		return base64_encode($enc);
	}

	// --------------------------------------------------------------------

	/**
	 * Decode
	 *
	 * Reverses the above process
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function decode($string, $key = '')
	{
		$key = $this->get_key($key);

		if (preg_match('/[^a-zA-Z0-9\/\+=]/', $string))
		{
			return FALSE;
		}

		$dec = base64_decode($string);

		if ($this->_mcrypt_exists === TRUE)
		{
			if (($dec = $this->mcrypt_decode($dec, $key)) === FALSE)
			{
				return FALSE;
			}
		}
		else
		{
			$dec = $this->_xor_decode($dec, $key);
		}

		return $dec;
	}

	// --------------------------------------------------------------------

	/**
	 * Encode from Legacy
	 *
	 * Takes an encoded string from the original Encryption class algorithms and
	 * returns a newly encoded string using the improved method added in 2.0.0
	 * This allows for backwards compatibility and a method to transition to the
	 * new encryption algorithms.
	 *
	 * For more details, see http://codeigniter.com/user_guide/installation/upgrade_200.html#encryption
	 *
	 * @access	public
	 * @param	string
	 * @param	string	legacy mode (unused in openssl, kept for API compatibility)
	 * @param	string
	 * @return	string
	 */
	function encode_from_legacy($string, $legacy_mode = '', $key = '')
	{
		if ($this->_mcrypt_exists === FALSE)
		{
			log_message('error', 'Encoding from legacy is available only when OpenSSL is in use.');
			return FALSE;
		}

		// decode it first
		$current_mode = $this->_get_mode();

		$key = $this->get_key($key);

		if (preg_match('/[^a-zA-Z0-9\/\+=]/', $string))
		{
			return FALSE;
		}

		$dec = base64_decode($string);

		if (($dec = $this->mcrypt_decode($dec, $key)) === FALSE)
		{
			return FALSE;
		}

		$dec = $this->_xor_decode($dec, $key);

		// set the mode back
		$this->set_mode($current_mode);

		// and re-encode
		return base64_encode($this->mcrypt_encode($dec, $key));
	}

	// --------------------------------------------------------------------

	/**
	 * XOR Encode
	 *
	 * Takes a plain-text string and key as input and generates an
	 * encoded bit-string using XOR
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function _xor_encode($string, $key)
	{
		$rand = '';
		while (strlen($rand) < 32)
		{
			$rand .= mt_rand(0, mt_getrandmax());
		}

		$rand = $this->hash($rand);

		$enc = '';
		for ($i = 0; $i < strlen($string); $i++)
		{
			$enc .= substr($rand, ($i % strlen($rand)), 1).(substr($rand, ($i % strlen($rand)), 1) ^ substr($string, $i, 1));
		}

		return $this->_xor_merge($enc, $key);
	}

	// --------------------------------------------------------------------

	/**
	 * XOR Decode
	 *
	 * Takes an encoded string and key as input and generates the
	 * plain-text original message
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function _xor_decode($string, $key)
	{
		$string = $this->_xor_merge($string, $key);

		$dec = '';
		for ($i = 0; $i < strlen($string); $i++)
		{
			$dec .= (substr($string, $i++, 1) ^ substr($string, $i, 1));
		}

		return $dec;
	}

	// --------------------------------------------------------------------

	/**
	 * XOR key + string Combiner
	 *
	 * Takes a string and key as input and computes the difference using XOR
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function _xor_merge($string, $key)
	{
		$hash = $this->hash($key);
		$str = '';
		for ($i = 0; $i < strlen($string); $i++)
		{
			$str .= substr($string, $i, 1) ^ substr($hash, ($i % strlen($hash)), 1);
		}

		return $str;
	}

	// --------------------------------------------------------------------

	/**
	 * Encrypt using OpenSSL (replaces mcrypt_encode)
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function mcrypt_encode($data, $key)
	{
		$cipher = $this->_get_cipher();
		$iv_size = openssl_cipher_iv_length($cipher);
		$init_vect = openssl_random_pseudo_bytes($iv_size);
		$encrypted = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $init_vect);
		return $this->_add_cipher_noise($init_vect . $encrypted, $key);
	}

	// --------------------------------------------------------------------

	/**
	 * Decrypt using OpenSSL (replaces mcrypt_decode)
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function mcrypt_decode($data, $key)
	{
		$data = $this->_remove_cipher_noise($data, $key);
		$cipher = $this->_get_cipher();
		$iv_size = openssl_cipher_iv_length($cipher);

		if ($iv_size > strlen($data))
		{
			return FALSE;
		}

		$init_vect = substr($data, 0, $iv_size);
		$data = substr($data, $iv_size);
		return openssl_decrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $init_vect);
	}

	// --------------------------------------------------------------------

	/**
	 * Adds permuted noise to the IV + encrypted data to protect
	 * against Man-in-the-middle attacks on CBC mode ciphers
	 * http://www.ciphersbyritter.com/GLOSSARY.HTM#IV
	 *
	 * Function description
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function _add_cipher_noise($data, $key)
	{
		$keyhash = $this->hash($key);
		$keylen = strlen($keyhash);
		$str = '';

		for ($i = 0, $j = 0, $len = strlen($data); $i < $len; ++$i, ++$j)
		{
			if ($j >= $keylen)
			{
				$j = 0;
			}

			$str .= chr((ord($data[$i]) + ord($keyhash[$j])) % 256);
		}

		return $str;
	}

	// --------------------------------------------------------------------

	/**
	 * Removes permuted noise from the IV + encrypted data, reversing
	 * _add_cipher_noise()
	 *
	 * Function description
	 *
	 * @access	public
	 * @param	type
	 * @return	type
	 */
	function _remove_cipher_noise($data, $key)
	{
		$keyhash = $this->hash($key);
		$keylen = strlen($keyhash);
		$str = '';

		for ($i = 0, $j = 0, $len = strlen($data); $i < $len; ++$i, ++$j)
		{
			if ($j >= $keylen)
			{
				$j = 0;
			}

			$temp = ord($data[$i]) - ord($keyhash[$j]);

			if ($temp < 0)
			{
				$temp = $temp + 256;
			}

			$str .= chr($temp);
		}

		return $str;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the Cipher
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	function set_cipher($cipher)
	{
		$this->_mcrypt_cipher = $cipher;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the Mode
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	function set_mode($mode)
	{
		$this->_mcrypt_mode = $mode;
	}

	// --------------------------------------------------------------------

	/**
	 * Get OpenSSL cipher Value
	 *
	 * @access	private
	 * @return	string
	 */
	function _get_cipher()
	{
		return $this->_openssl_cipher;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Mode Value (kept for API compatibility)
	 *
	 * @access	private
	 * @return	string
	 */
	function _get_mode()
	{
		if ($this->_mcrypt_mode == '')
		{
			$this->_mcrypt_mode = 'cbc';
		}

		return $this->_mcrypt_mode;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the Hash type
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function set_hash($type = 'sha1')
	{
		$this->_hash_type = ($type != 'sha1' AND $type != 'md5') ? 'sha1' : $type;
	}

	// --------------------------------------------------------------------

	/**
	 * Hash encode a string
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function hash($str)
	{
		return ($this->_hash_type == 'sha1') ? $this->sha1($str) : md5($str);
	}

	// --------------------------------------------------------------------

	/**
	 * Generate an SHA1 Hash
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function sha1($str)
	{
		return sha1($str);
	}

}

// END CI_Encrypt class

/* End of file Encrypt.php */
/* Location: ./system/libraries/Encrypt.php */
