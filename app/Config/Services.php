<?php namespace Config;

use CodeIgniter\Config\Services as CoreServices;
use CodeIgniter\Config\BaseConfig;

require_once SYSTEMPATH . 'Config/Services.php';

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *服务只是系统用来完成其工作的其他类/库。CodeIgniter使用它可以轻松地交换框架的核心，而不会影响应用程序其余部分的使用。
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 * 此文件保存您可能需要的任何特定于应用程序的服务或服务重写。服务方法应使用的通用方法格式中包含了一个示例。有关更多示例，请参阅位于system/Config/Services.php的核心服务文件。
 */
class Services extends CoreServices
{

	    public static function example($getShared = true)
	    {
	        if ($getShared)
	        {
	            return static::getSharedInstance('example');
	        }

	        return new \CodeIgniter\Example();
	    }
}
