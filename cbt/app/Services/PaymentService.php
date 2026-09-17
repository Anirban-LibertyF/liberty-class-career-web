<?php
namespace App\Services;
use App\Core\{Database,HttpException};
final class PaymentService {
 private function credentials(): array {
  if(($_ENV['PAYMENT_DRIVER']??'')!=='razorpay')throw new HttpException(503,'Online payment is temporarily unavailable.');
  $key=trim((string)($_ENV['RAZORPAY_KEY_ID']??''));$secret=trim((string)($_ENV['RAZORPAY_KEY_SECRET']??''));
  if($key===''||$secret==='')throw new HttpException(503,'Online payment is not configured.');
  return [$key,$secret];
 }
 private function request(string $method,string $path,?array $payload=null): array {
  [$key,$secret]=$this->credentials();if(!function_exists('curl_init'))throw new HttpException(503,'Payment service is unavailable.');
  $curl=curl_init('https://api.razorpay.com/v1'.$path);$headers=['Accept: application/json'];
  if($payload!==null){$headers[]='Content-Type: application/json';curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($payload,JSON_THROW_ON_ERROR));}
  curl_setopt_array($curl,[CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_RETURNTRANSFER=>true,CURLOPT_HTTPAUTH=>CURLAUTH_BASIC,CURLOPT_USERPWD=>$key.':'.$secret,CURLOPT_HTTPHEADER=>$headers,CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>20]);
  $body=curl_exec($curl);$status=(int)curl_getinfo($curl,CURLINFO_RESPONSE_CODE);$error=curl_error($curl);curl_close($curl);
  if($body===false||$error!=='')throw new HttpException(502,'Could not connect to Razorpay. Please try again.');
  $data=json_decode($body,true);if($status<200||$status>=300||!is_array($data))throw new HttpException(502,'Razorpay could not process this payment. Please try again.');
  return $data;
 }
 public function enroll(int $studentId,array $test): array {
  $db=Database::connection();$db->beginTransaction();try{
   $q=$db->prepare('SELECT id,status FROM enrollments WHERE student_id=? AND test_id=? FOR UPDATE');$q->execute([$studentId,$test['id']]);if($e=$q->fetch()){$db->commit();return ['enrollment_id'=>(int)$e['id'],'status'=>$e['status']];}
   $free=(float)$test['fee']<=0;$q=$db->prepare("INSERT INTO enrollments(student_id,test_id,status,enrolled_at) VALUES(?,?,?,NOW())");$q->execute([$studentId,$test['id'],$free?'active':'payment_pending']);$id=(int)$db->lastInsertId();
   if(!$free){$reference='pending_'.bin2hex(random_bytes(12));$p=$db->prepare("INSERT INTO payments(enrollment_id,provider,provider_order_id,amount,currency,status,created_at,updated_at) VALUES(?,'razorpay',?,?,'INR','pending',NOW(),NOW())");$p->execute([$id,$reference,$test['fee']]);}
   AuditService::log('student',$studentId,'new_enrollment','Student enrolled in test',['test_id'=>(int)$test['id'],'enrollment_id'=>$id,'status'=>$free?'active':'payment_pending']);$db->commit();return ['enrollment_id'=>$id,'status'=>$free?'active':'payment_pending'];
  }catch(\Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
 }
 public function checkout(int $enrollmentId,int $studentId): array {
  $db=Database::connection();$q=$db->prepare("SELECT p.*,e.student_id,t.title,t.starts_at,t.ends_at,t.duration_minutes,s.name student_name,s.email student_email,s.phone student_phone FROM payments p JOIN enrollments e ON e.id=p.enrollment_id JOIN tests t ON t.id=e.test_id JOIN students s ON s.id=e.student_id WHERE e.id=? AND e.student_id=? AND e.status='payment_pending' AND p.status='pending' ORDER BY p.id DESC LIMIT 1");$q->execute([$enrollmentId,$studentId]);$payment=$q->fetch();if(!$payment)throw new HttpException(404,'Pending payment not found.');
  [$key]=$this->credentials();if(!str_starts_with((string)$payment['provider_order_id'],'order_')){
   $amount=(int)round((float)$payment['amount']*100);if($amount<100)throw new HttpException(422,'Razorpay requires a minimum payment of INR 1.00.');
   $order=$this->request('POST','/orders',['amount'=>$amount,'currency'=>$payment['currency'],'receipt'=>'LCC-'.$payment['id'].'-'.bin2hex(random_bytes(5)),'notes'=>['enrollment_id'=>(string)$enrollmentId]]);
   if(empty($order['id'])||!str_starts_with((string)$order['id'],'order_'))throw new HttpException(502,'Razorpay did not return a valid order.');
   $update=$db->prepare("UPDATE payments SET provider='razorpay',provider_order_id=?,updated_at=NOW() WHERE id=? AND status='pending'");$update->execute([$order['id'],$payment['id']]);$payment['provider']='razorpay';$payment['provider_order_id']=$order['id'];
  }
  $payment['checkout_key']=$key;$payment['amount_subunits']=(int)round((float)$payment['amount']*100);return $payment;
 }
 public function verify(int $enrollmentId,int $studentId,array $response): void {
  $paymentId=trim((string)($response['razorpay_payment_id']??''));$orderId=trim((string)($response['razorpay_order_id']??''));$signature=trim((string)($response['razorpay_signature']??''));
  if(!preg_match('/^pay_[A-Za-z0-9]+$/',$paymentId)||!preg_match('/^order_[A-Za-z0-9]+$/',$orderId)||!preg_match('/^[a-f0-9]{64}$/i',$signature))throw new HttpException(422,'Razorpay returned incomplete payment verification data.');
  $db=Database::connection();$q=$db->prepare("SELECT p.*,e.student_id,e.status enrollment_status FROM payments p JOIN enrollments e ON e.id=p.enrollment_id WHERE e.id=? AND e.student_id=? ORDER BY p.id DESC LIMIT 1");$q->execute([$enrollmentId,$studentId]);$payment=$q->fetch();if(!$payment)throw new HttpException(404,'Payment not found.');if($payment['status']==='paid')return;if(!hash_equals((string)$payment['provider_order_id'],$orderId))throw new HttpException(422,'Payment order verification failed.');
  [, $secret]=$this->credentials();$expected=hash_hmac('sha256',$payment['provider_order_id'].'|'.$paymentId,$secret);if(!hash_equals($expected,$signature))throw new HttpException(422,'Payment signature verification failed.');
  $remote=$this->request('GET','/payments/'.rawurlencode($paymentId));$amount=(int)round((float)$payment['amount']*100);
  if(($remote['order_id']??'')!==$payment['provider_order_id']||(int)($remote['amount']??-1)!==$amount||($remote['currency']??'')!==$payment['currency']||($remote['status']??'')!=='captured')throw new HttpException(409,'Payment is not captured yet. Please check Razorpay and try again.');
  $safe=['id'=>$remote['id']??$paymentId,'order_id'=>$remote['order_id']??$orderId,'status'=>$remote['status']??'','amount'=>$remote['amount']??$amount,'currency'=>$remote['currency']??$payment['currency'],'method'=>$remote['method']??null];
  $db->beginTransaction();try{$lock=$db->prepare('SELECT status FROM payments WHERE id=? FOR UPDATE');$lock->execute([$payment['id']]);if($lock->fetchColumn()!=='paid'){$db->prepare("UPDATE payments SET provider_payment_id=?,status='paid',verified_at=NOW(),raw_response=?,updated_at=NOW() WHERE id=?")->execute([$paymentId,json_encode($safe,JSON_THROW_ON_ERROR),$payment['id']]);$db->prepare("UPDATE enrollments SET status='active' WHERE id=? AND student_id=?")->execute([$enrollmentId,$studentId]);AuditService::log('student',$studentId,'payment_verified','Razorpay payment verified',['enrollment_id'=>$enrollmentId,'payment_id'=>$paymentId]);}$db->commit();}catch(\Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
 }
}
