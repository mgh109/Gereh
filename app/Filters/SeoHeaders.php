<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SeoHeaders implements FilterInterface
{
    public function before(RequestInterface $request,$arguments=null) {}
    public function after(RequestInterface $request,ResponseInterface $response,$arguments=null)
    {
        $path=trim($request->getUri()->getPath(),'/');
        $base=trim((string)parse_url(config('App')->baseURL,PHP_URL_PATH),'/');
        if($base!==''&&str_starts_with($path,$base.'/'))$path=substr($path,strlen($base)+1);
        if($response->getStatusCode()>=400 || preg_match('#^(admin|api|cart|checkout|login|account|favorites|payment)(/|$)#i',$path))$response->setHeader('X-Robots-Tag','noindex, nofollow');
    }
}
