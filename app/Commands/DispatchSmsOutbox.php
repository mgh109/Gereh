<?php
namespace App\Commands;
use App\Services\SmsNotificationService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
class DispatchSmsOutbox extends BaseCommand {
 protected $group='GEREH';
 protected $name='gereh:sms-dispatch';
 protected $description='Send queued transactional SMS after explicit server authorization.';
 public function run(array $params) {
  if((string)env('GEREH_SMS_DISPATCH_ENABLED','false')!=='true') {
   CLI::error('SMS dispatcher disabled by server config.');
   return;
  }
  try {
   $result=(new SmsNotificationService())->dispatch(20);
   CLI::write(json_encode($result));
  } catch(\Throwable $e) {
   log_message('error','SMS dispatch failed: {type}',['type'=>get_class($e)]);
   CLI::error('SMS dispatcher failed.');
  }
 }
}