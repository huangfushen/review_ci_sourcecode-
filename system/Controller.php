<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019 CodeIgniter Foundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter;

use Config\Services;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Validation\Validation;
use CodeIgniter\Validation\Exceptions\ValidationException;
use Psr\Log\LoggerInterface;

/**
 * Class Controller
 *
 * @package CodeIgniter
 */
class Controller
{

	/**
	 * An array of helpers to be automatically loaded
     * 一系列自动加载的帮助程序
	 * upon class instantiation.
	 *在类实例化时
     * @var array
	 */
	protected $helpers = [];

	//--------------------------------------------------------------------

	/**
	 * Instance of the main Request object.
	 *主Request对象的实例
	 * @var HTTP\IncomingRequest
	 */
	protected $request;

	/**
	 * Instance of the main response object.
	 *主要响应对象的实例
	 * @var HTTP\Response
	 */
	protected $response;

	/**
	 * Instance of logger to use.
	 *要使用的记录器实例
	 * @var Log\Logger
	 */
	protected $logger;

	/**
	 * Whether HTTPS access should be enforced
     * 是否应强制执行HTTPS访问
	 * for all methods in this controller.
	 *用于此控制器中的所有方法
	 * @var integer  Number of seconds to set HSTS header
     * 整数  设置HSTS标头的秒数
	 */
	protected $forceHTTPS = 0;

	/**
	 * Once validation has been run,
     * 运行验证后
	 * will hold the Validation instance.
	 *将保存Validation实例
	 * @var Validation
	 */
	protected $validator;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 * @param RequestInterface         $request
	 * @param ResponseInterface        $response
	 * @param \Psr\Log\LoggerInterface $logger
	 *
	 * @throws \CodeIgniter\HTTP\Exceptions\HTTPException
	 */
	public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
	{
		$this->request  = $request;
		$this->response = $response;
		$this->logger   = $logger;
		$this->logger->info('Controller "' . get_class($this) . '" loaded.');

		if ($this->forceHTTPS > 0)
		{
			$this->forceHTTPS($this->forceHTTPS);
		}

		$this->loadHelpers();
	}

	//--------------------------------------------------------------------

	/**
	 * A convenience method to use when you need to ensure that a single
	 * method is reached only via HTTPS. If it isn't, then a redirect
	 * will happen back to this method and HSTS header will be sent
	 * to have modern browsers transform requests automatically.
	 *
     *一种方便的方法，当您需要确保单个
     *方法仅通过HTTPS访问。 如果不是，则重定向
     *将返回此方法，并发送HSTS标头
     *使现代浏览器自动转换请求。
     *
	 * @param integer $duration The number of seconds this link should be
	 *                          considered secure for. Only with HSTS header.
	 *                          Default value is 1 year.
	 *应该将此链接视为安全的秒数。 仅带有HSTS标头。默认值为1年。
     * @throws \CodeIgniter\HTTP\Exceptions\HTTPException
	 */
	protected function forceHTTPS(int $duration = 31536000)
	{
		force_https($duration, $this->request, $this->response);
	}

	//--------------------------------------------------------------------

	/**
	 * Provides a simple way to tie into the main CodeIgniter class
	 * and tell it how long to cache the current page for.
	 *提供一种简单的方法来绑定到主要的CodeIgniter类，并告诉它为当前页面缓存多长时间。
	 * @param integer $time
	 */
	protected function cachePage(int $time)
	{
		CodeIgniter::cache($time);
	}

	//--------------------------------------------------------------------

	/**
	 * Handles "auto-loading" helper files.
     * 处理“自动加载”帮助文件。
     */
	protected function loadHelpers()
	{
		if (empty($this->helpers))
		{
			return;
		}

		foreach ($this->helpers as $helper)
		{
			helper($helper);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * A shortcut to performing validation on input data. If validation
	 * is not successful, a $errors property will be set on this class.
	 *对输入数据执行验证的快捷方式。 如果验证不成功，将在此类上设置$ errors属性。
	 * @param array|string $rules
	 * @param array        $messages An array of custom error messages
	 *
	 * @return boolean
	 */
	protected function validate($rules, array $messages = []): bool
	{
		$this->validator = Services::validation();

		// If you replace the $rules array with the name of the group
        //如果将$ rules数组替换为组名
		if (is_string($rules))
		{
			$validation = new \Config\Validation();

			// If the rule wasn't found in the \Config\Validation, we
			// should throw an exception so the developer can find it.
            //如果未在\ Config \ Validation中找到该规则，则应抛出异常，以便开发人员可以找到它。
			if (! isset($validation->$rules))
			{
				throw ValidationException::forRuleNotFound($rules);
			}

			// If no error message is defined, use the error message in the Config\Validation file
            //如果未定义错误消息，请使用Config \ Validation文件中的错误消息
			if (! $messages)
			{
				$errorName = $rules . '_errors';
				$messages  = $validation->$errorName ?? [];
			}

			$rules = $validation->$rules;
		}

		$success = $this->validator
			->withRequest($this->request)
			->setRules($rules, $messages)
			->run();

		return $success;
	}

	//--------------------------------------------------------------------
}
