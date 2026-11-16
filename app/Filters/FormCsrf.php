<?php
namespace App\Filters;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Security\Exceptions\SecurityException;
class FormCsrf extends \CodeIgniter\Filters\CSRF
{
    public function before(RequestInterface $request,$arguments=null)
    {
        try{return parent::before($request,$arguments);}catch(SecurityException $e){
            $message='اعتبار فرم به پایان رسیده است. لطفاً دوباره تلاش کنید.';
            if($request->isAJAX()||str_contains($request->getHeaderLine('Accept'),'application/json')||str_starts_with(ltrim($request->getPath(),'/'),'api/'))return service('response')->setStatusCode(403)->setJSON(['success'=>false,'message'=>$message,'data'=>null]);
            // Reject the failed request; do not automatically replay a mutation.
            $base=parse_url(site_url());$ref=parse_url($request->getHeaderLine('Referer'));
            $target=str_starts_with(ltrim($request->getPath(),'/'),'admin/')?'admin':'/';
            if(is_array($ref)&&($ref['host']??'')===($base['host']??'')&&($ref['scheme']??'')===($base['scheme']??'')&&($ref['port']??null)===($base['port']??null))$target=ltrim($ref['path']??'/','/').(isset($ref['query'])?'?'.$ref['query']:'');
            return redirect()->to(site_url($target))->with('error',$message)->with('comment_error',$message);
        }
    }
}
