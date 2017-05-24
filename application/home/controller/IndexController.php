<?php

namespace app\home\controller;

use app\common\controller\Common;


use app\common\logic\User;
use app\common\model\Attachment;
use app\common\model\PriceUnitType;
use app\common\model\Task;
use app\common\model\TaskApply;
use app\common\model\TaskStatus;
use app\common\model\UserAccount;
use app\common\logic\MerchantMoneyLogic;
use app\merchant\logic\MerchantLogic;
use app\merchant\logic\TaskLogic;
use app\merchant\logic\UserMoneyAccount;
use app\merchant\model\MerchantEmployee;
use app\merchant\model\MerchantInfo;
use app\waimai\model\OtherOrder;
use baidu\Curl;
use pay\Encryption;
use pay\WeEncryption;
use think\Db;
use app\home\model\DataDictionary;
use app\merchant\model\DataDictionary as DataLib;

class Api extends Common
{

    /**
     * 登录接口
     * @return \think\response\Json
     */
    public function login()
    {
        return $this->doLogin();
    }

    /**
     * 获取城市
     * @return \think\response\Json
     */
    public function getCity()
    {
        $dataDic = new DataDictionary();
        $data = $dataDic->getCity(input('pid', 1));
        if ($data) {
            $this->ajaxMsg['code'] = self::RESULT_SUCCESS;
            $this->ajaxMsg['msg'] = $this->msg[$this->ajaxMsg['code']];
            $this->ajaxMsg['resultList'] = $data;
        }
        return json($this->ajaxMsg);
    }

    /**
     * 获取区域
     * @return \think\response\Json
     */
    public function getArea()
    {
        $dataDic = new DataDictionary();
        $data = $dataDic->getArea(input('cid'));
        if ($data && is_array($data)) {
            $this->ajaxMsg['code'] = self::RESULT_SUCCESS;
            $this->ajaxMsg['msg'] = $this->msg[$this->ajaxMsg['code']];
            $this->ajaxMsg['resultList'] = $data;
        }
        return json($this->ajaxMsg);
    }


    /**
     * 获取短信
     */
    public function getRegisterCode()
    {
        $type = input('post.type', 1);
        return $this->registerCode($type);
    }

    /**
     * 注册处理
     */
    public function doRegister()
    {
        $mobile = input('post.login_name');
        $validateCode = input('post.code');
        $data = [
            'version' => self::VERSION,
            'login_name' => $mobile,
            'login_pass' => input('post.login_pass'),
            'mobile' => $mobile,
           //  'city_id' => input('post.city_id'),
          //  'area_id' => input('post.area_id'),
            'create_date' => date('Y-m-d H:i:s'),
         //   'province_id' => 1,
            'alipay_yn' => 1,
            'is_activation' => 0,
        ];
        return parent::dealRegister($data, $mobile, $validateCode);
    }

    /**
     * 完善商户版信息
     */
    public function addMerchantInfo()
    {
        $userId = input('post.mc_tb_user_id');
        $this->ajaxMsg['code'] = self::RESULT_ERROR;
        $this->ajaxMsg['msg'] = "用户不存在";

        if (false == is_numeric($userId) || $userId <= 0) {
            $this->ajaxMsg['msg'] = "用户参数错误";
            return json($this->ajaxMsg);
        }

        $userAccount = new UserAccount();
        $att = new Attachment();
        if ($user = $userAccount->isExistsById($userId)) {
            $merchantInfo = new MerchantInfo();
            if (($info = $merchantInfo->isMerchant($userId)) || ($info = $merchantInfo->checkFail($userId)) || ($info = $merchantInfo->waitToAddMore($userId))) {
                $taskLogic = new TaskLogic();
                if(false == $taskLogic->canChangeMerchantInfo($info['id'])){
                    $this->ajaxMsg['msg'] = "您尚有订单未完成";
                    return json($this->ajaxMsg);
                }

                $data = input('post.');
                unset($data['client']);
                unset($data['real_name']);
                unset($data['id_card_num']);
                $data['mc_status'] = self::WAIT_CHECK;
                $data['update_date'] = date("Y-m-d H:i:s");

                if(array_key_exists('hold_front_card_img',$data)) $data['hold_front_card_img'] = $att->addAtt($data['hold_front_card_img'], self::VERSION);
                if(array_key_exists('hold_back_card_img',$data)) $data['hold_back_card_img'] = $att->addAtt($data['hold_back_card_img'], self::VERSION);
                if(array_key_exists('merchant_img',$data)) $data['merchant_img'] = $att->addAtt($data['merchant_img'], self::VERSION);

                $merchantUpdate = $merchantInfo->allowField(true)->isUpdate(true)->save($data, ['id' => $info['id']]);

                if(input('post.real_name') && input('post.id_card_num')){
                    $userData = [
                        'real_name' =>input('post.real_name'),
                        'id_card_num' =>input('post.id_card_num'),
                        'update_date' => date('Y-m-d H:i:s')
                    ];
                    $userUpdate = $userAccount->updateInfo($userData, $userId);
                }else{
                    $userUpdate = true;
                }

                if ($merchantUpdate || $userUpdate) {
                    $this->ajaxMsg['code'] = self::RESULT_SUCCESS;
                    $this->ajaxMsg['msg'] = $this->msg[$this->ajaxMsg['code']];
                } else {
                    $this->ajaxMsg['code'] = self::RESULT_STATUS_EXCEPTION;
                    $this->ajaxMsg['msg'] = '更新失败';
                }
            } elseif ($merchantInfo->isApplying($userId)) { //商户信息审核中
                $this->ajaxMsg['code'] = self::RESULT_STATUS_EXCEPTION;
                $this->ajaxMsg['msg'] = '商户信息审核中，请勿重复提交';
            } else {

                $data = input('post.');
                unset($data['client']);
                unset($data['real_name']);
                unset($data['id_card_num']);
                $data['mc_invite_code'] = create_invite_unique_code();
                $data['version'] = self::VERSION;
                $data['mc_status'] = self::WAIT_CHECK;
                $data['mc_contact_mobile'] = $user['mobile'];
                $data['province_id'] = 1;
                $data['city_id'] = input('post.city_id');
                $data['area_id'] = input('post.area_id');
                $data['mc_type'] = (isset($data['mc_type']) && $data['mc_type'])?$data['mc_type']:3;
                $data['create_date'] = date('Y-m-d H:i:s');

                if(array_key_exists('hold_front_card_img',$data)) $data['hold_front_card_img'] = $att->addAtt($data['hold_front_card_img'], self::VERSION);
                if(array_key_exists('hold_back_card_img',$data)) $data['hold_back_card_img'] = $att->addAtt($data['hold_back_card_img'], self::VERSION);
                if(array_key_exists('merchant_img',$data)) $data['merchant_img'] = $att->addAtt($data['merchant_img'], self::VERSION);

                $merchantUpdate = $merchantInfo->allowField(true)->insert($data) ;

                if(input('post.real_name') && input('post.id_card_num')){
                    $userData = [
                        'real_name' =>input('post.real_name'),
                        'id_card_num' =>input('post.id_card_num'),
                        'update_date' => date('Y-m-d H:i:s')
                    ];
                    $userUpdate = $userAccount->updateInfo($userData, $userId);
                }else{
                    $userUpdate = true;
                }

                if ($merchantUpdate || $userUpdate) {
                    $this->ajaxMsg['code'] = self::RESULT_SUCCESS;
                    $this->ajaxMsg['msg'] = $this->msg[$this->ajaxMsg['code']];
                } else {
                    $this->ajaxMsg['code'] = self::RESULT_STATUS_EXCEPTION;
                    $this->ajaxMsg['msg'] = '更新失败';
                }
            }
        }
        return json($this->ajaxMsg);
    }

    /**
     * 完善账户信息
     * @return \think\response\Json
     */
    public function addAccoutInfo()
    {
        $userId = input('post.mc_tb_user_id');

        $this->ajaxMsg['code'] = self::RESULT_ERROR;
        $this->ajaxMsg['msg'] = "用户不存在";

        if (false == is_numeric($userId) || $userId <= 0) {
            $this->ajaxMsg['msg'] = "用户参数错误";
            return json($this->ajaxMsg);
        }
        $userAccount = new UserAccount();
        if ($info = $userAccount->isExistsById($userId)) {
            $merchantModel= new MerchantInfo();
            if (false == $merchantModel->isMerchant($userId)) {
                $this->ajaxMsg['code'] = self::RESULT_STATUS_EXCEPTION;
                $this->ajaxMsg['msg'] = '只有店主才能修改店铺信息';
            } else {
                $data = [
                    'real_name' =>input('post.real_name'),
                    'id_card_num' =>input('post.id_card_num'),
                    'update_date' => date('Y-m-d H:i:s')
                ];
                $merchantData = [
                    'hold_front_card_img' => input('post.hold_front_card_img'),
                    'hold_back_card_img' => input('post.hold_back_card_img'),
                    'merchant_img' => input('post.merchant_img'),
                ];
                $merchantLogic = new MerchantLogic();
                if ($userAccount->updateInfo($data, $userId) || $merchantLogic->addImg($merchantData, $userId)) {
                    $this->ajaxMsg['code'] = self::RESULT_SUCCESS;
                    $this->ajaxMsg['msg'] = $this->msg[$this->ajaxMsg['code']];
                } else {
                    $this->ajaxMsg['code'] = self::RESULT_STATUS_EXCEPTION;
                    $this->ajaxMsg['msg'] = '操作失败';
                }
            }
        }
        return json($this->ajaxMsg);
    }

    /**
     * 新增任务
     * @return \think\response\Json
     */
    public function addTask()
    {
        $userType = input('post.user_type');
        $userId = input('post.create_id');
        $data = input('post.');
        if (false == is_numeric($userId) || $userId <= 0 || (!isset($data['receipt_address'])) || (!isset($data['receipt_address_detail']))) {
            $this->ajaxMsg['msg'] = "用户参数错误";
            return json($this->ajaxMsg);
        }
        $userLogic = new User();
        if ($userInfo = $userLogic->dealUserInfo($userId)) {
            $merchantModel = new MerchantInfo();
            $employeeModel = new MerchantEmployee();

            $employeeInfo = $merchantInfo = array();

            if (self::MERCHANT == $userType) {
                $merchantInfo = $merchantModel->isMerchant($userId);
            } elseif (self::EMPLOYEE == $userType) {
                $employeeInfo = $employeeModel->isEmployee($userId);
                $merchantInfo = $merchantModel->isMerchantById($employeeInfo['mc_id']);
            }

            $typeFlag = $this->checkUser($merchantInfo, $employeeInfo, $userType);

            if ($typeFlag) {
                $taskModel = new Task();
                $data['creator'] = $merchantInfo['id'];
                $data['area_id'] = $userInfo['area_id'];
                $data['city_id'] = $userInfo['city_id'];
                $data['province_id'] = $userInfo['province_id'] ? $userInfo['province_id'] : 1;
                $data['create_name'] = $userId;
                $data['title'] = $merchantInfo['merchat_name'] . '发布订单';
                $data['area_details'] = $merchantInfo['mc_location_detail'];
                $data['task_detail'] = $merchantInfo['mc_location_detail'] . ',' . $data['receipt_address'] . $data['receipt_address_detail'];
                if(!isset($data['price_units'])){
                    $data['price_units'] = 1;
                }
                if(!isset($data['extra_cost_unit'])){
                    $data['extra_cost_unit'] = 1;
                }
                if(isset($merchantInfo['pay_method']) && 1 == $merchantInfo['pay_method']){
                    if(isset($data['out_trade_num']) && isset($data['source'])){
                        if($taskModel->isExist($data['out_trade_num'], $data['source'])){
                            $this->ajaxMsg['code'] = self::RESULT_IS_EXIST;
                            $this->ajaxMsg['msg'] = "订单重复";
                            return json($this->ajaxMsg);
                        }
                    }

                    //查询客户端经纬度
                    $lMerchant = new MerchantLogic();
                    $dataDic = new DataLib();

                    $where = ['id'=>$userInfo['city_id']];
                    $field = 'description, base_price, per_price';
                    $cityData = $dataDic->getOne($where, $field);
                    $location = $lMerchant->getPoints($data['receipt_address_detail'], $cityData['description']);
                    if(is_array($data)){
                        $data['recipient_baidu_longitude'] = $location['lng'];
                        $data['recipient_baidu_latitude'] = $location['lat'];
                    }

                    $data['pay_method'] = 1;
                    $data['price'] = $merchantInfo['base_price'];
                    $data['task_status'] = TaskStatus::PUBLISHED;
                    if(isset($data['extra_cost']))unset($data['extra_cost']);
                    if(isset($data['distance_price']))unset($data['distance_price']);
                    if(isset($data['night_fee']))unset($data['night_fee']);
                }else{
                    $data['task_status'] = TaskStatus::WAIT_PAY;
                }
                $data['version'] = self::VERSION;
                $data['isshow'] = Task::SHOW;
                $data['total_person_num'] = 1;
                $data['in_person_num'] = 0;
                $data['pay_no'] = getIdentifier();
                $data['publish_date'] = $data['start_date'] = $data['create_date'] = date('Y-m-d H:i:s');
                $data['task_id'] = getIdentifier();
                $data['enterprice_id'] = 1000;
                unset($data['client']);
                unset($data['user_type']);
                unset($data['create_id']);

                if ($taskModel->allowField(true)->insert($data)) {
                    $this->ajaxMsg['code'] = self::RESULT_INSERT_SUCCESS;
                    $this->ajaxMsg['msg'] = "发布成功";
                    //Todo
                    $extraCost = isset($data['extra_cost'])?$data['extra_cost']:0;
                    $distancePrice = isset($data['distance_price'])?$data['distance_price']:0;
                    $request_data = [
                        'pay_no' => $data['pay_no'],
                        'alipay_server' => config('domain') . '/home/pay/alipay_notify',
                        'weixin_server' => config('domain') . '/home/pay/weixin_notify',
                        'total_fee' => $extraCost + $distancePrice + $data['price'],
                        'subject' => $data['title'],
                        'body' => '配送费',
                    ];

                    error_log(time().'|alipay_request|'.serialize($request_data)."\r\n",3,RUNTIME_PATH.'alipay_notify.log');

                    $this->ajaxMsg['resultDao'] = $request_data;
                } else {
                    $this->ajaxMsg['code'] = self::RESULT_STATUS_EXCEPTION;
                    $this->ajaxMsg['msg'] = "发布失败";
                }
            } else {
                $this->ajaxMsg['code'] = self::RESULT_ERROR;
                $this->ajaxMsg['msg'] = "权限不足";
            }
        } else {
            $this->ajaxMsg['code'] = self::RESULT_ERROR;
            $this->ajaxMsg['msg'] = "用户不存在";
        }
        return json($this->ajaxMsg);
    }

    /**
     * 任务列表
     * @return \think\response\Json
     */
    public function taskList()
    {
        $status = input('get.status');
        $userType = input('get.user_type');
        $userId = input('get.user_id');
        $pageSize = input('get.page_size');

        if (false == is_numeric($userId) || $userId <= 0) {
            $this->ajaxMsg['msg'] = "用户参数错误";
            return json($this->ajaxMsg);
        }
        $task = new Task();

        $merchantModel = new MerchantInfo();
        $employeeModel = new MerchantEmployee();

        $employeeInfo = $merchantInfo = array();

        if (self::MERCHANT == $userType) {
            $merchantInfo = $merchantModel->isMerchant($userId);
        } elseif (self::EMPLOYEE == $userType) {
            $employeeInfo = $employeeModel->isEmployee($userId);
            $merchantInfo = $merchantModel->isMerchantById($employeeInfo['mc_id']);
        }
        $typeFlag = $this->checkUser($merchantInfo, $employeeInfo, $userType);

        if ($typeFlag) {
            $taskApply = new TaskApply();
            if ($status == TaskStatus::WAIT_DELIVERY) { //5
                $result = $taskApply->getTaskByStatus(7, $merchantInfo['id'], $pageSize);
            } elseif ($status == TaskStatus::DELIVERYING) { //6
                $result = $taskApply->getTaskByStatus(2, $merchantInfo['id'], $pageSize);
            } elseif ($status == TaskStatus::PUBLISHED) {
                $result = $task->getNoneApplyData($merchantInfo['id'], $pageSize);
            } else {
                $result = $task->getTaskByStatus($status, $merchantInfo['id'], $pageSize);
            }
            if ($result) {
                $data = $result['data'];
                if ($data && is_array($data)) {
                    $priceTypeModel = new PriceUnitType();
                    $priceType = $priceTypeModel->getType();
                    if(TaskStatus::WAIT_DELIVERY == $status || TaskStatus::DELIVERYING){
                        $userAccount = new UserAccount();
                        foreach ($data as $key => $val) {
                            if($val['user_account_id']> 0){
                                $data[$key]['user_account_id'] = $userAccount->getRiderInfo($val['user_account_id']);
                            }else{
                                $data[$key]['user_account_id'] = null;
                            }
                            $data[$key]['total_price'] = ($val['price'] + $val['distance_price']+ $val['extra_cost']).'';
                            $data[$key]['price_units'] = $val['price_units'] ? $priceType[$val['price_units']] : $val['price_units'];
                            $data[$key]['extra_cost_unit'] = $val['extra_cost_unit'] ? $priceType[$val['extra_cost_unit']] : $val['extra_cost_unit'];
                        }
                    }else{
                        foreach ($data as $key => $val) {
                            $data[$key]['total_price'] = ($val['price'] + $val['distance_price']+ $val['extra_cost']).'';
                            $data[$key]['price_units'] = $val['price_units'] ? $priceType[$val['price_units']] : $val['price_units'];
                            $data[$key]['extra_cost_unit'] = $val['extra_cost_unit'] ? $priceType[$val['extra_cost_unit']] : $val['extra_cost_unit'];
                        }
                    }
                    $this->ajaxMsg['code'] = self::RESULT_SUCCESS;
                    $this->ajaxMsg['msg'] = "查询成功";
                    $this->ajaxMsg['resultList'] = $data;
                    $this->ajaxMsg['page'] = ['max_page' => $result['max_page'], 'current_page' => $result['current_page']];
                }
            }
        } else {
            $this->ajaxMsg['code'] = self::RESULT_ERROR;
            $this->ajaxMsg['msg'] = "权限不足";
        }
        return json($this->ajaxMsg);
    }

    /**
     * 任务详情
     * @return \think\response\Json
     */
    public function taskDetail()
    {
        $taskId = input('get.task_id');
        $userType = input('get.user_type');
        $userId = input('get.user_id');

        if (false == is_numeric($userId) || $userId <= 0) {
            $this->ajaxMsg['msg'] = "用户参数错误";
            return json($this->ajaxMsg);
        }

        $merchantModel = new MerchantInfo();
        $employeeModel = new MerchantEmployee();
        $employeeInfo = $merchantInfo = array();

        if (self::MERCHANT == $userType) {
            $merchantInfo = $merchantModel->isMerchant($userId);
        } elseif (self::EMPLOYEE == $userType) {
            $employeeInfo = $employeeModel->isEmployee($userId);
            $merchantInfo = $merchantModel->isMerchantById($employeeInfo['mc_id']);
        }

        $typeFlag = $this->checkUser($merchantInfo, $employeeInfo, $userType);

        if ($typeFlag) {
            $this->ajaxMsg['code'] = self::RESULT_SUCCESS;
            $this->ajaxMsg['msg'] = "";
            $task = new Task();

            $data = $task->getTaskDetail($taskId);
            if ($data) {
                $userAccount = new UserAccount();
                $priceTypeModel = new PriceUnitType();
                $priceType = $priceTypeModel->getType();
                $data['user_account_id'] = $userAccount->getRiderInfo($data['user_account_id']);
                $data['price_units'] = $data['price_units'] ? $priceType[$data['price_units']] : $data['price_units'];
                $data['extra_cost_unit'] = $data['extra_cost_unit'] ? $priceType[$data['extra_cost_unit']] : $data['extra_cost_unit'];
            }
            $this->ajaxMsg['resultDao'] = $data;
        } else {
            $this->ajaxMsg['code'] = self::RESULT_ERROR;
            $this->ajaxMsg['msg'] = "权限不足";
        }
        return json($this->ajaxMsg);
    }


    /**
     * 检查用户是否有权限
     * @param $merchantInfo
     * @param $employeeInfo
     * @param $userType
     * @return bool
     */
    private function checkUser($merchantInfo, $employeeInfo, $userType)
    {
        $typeFlag = false;

        if (self::MERCHANT == $userType) {
            if ($merchantInfo) {
                $typeFlag = true;
            }
        } elseif (self::EMPLOYEE == $userType) {
            if ($employeeInfo) {
                if ($merchantInfo) {
                    $typeFlag = true;
                }
            }
        }
        return $typeFlag;
    }

    /**
     * 微信支付统一下单接口
     * @return bool
     */
    public function weixinPay()
    {
        $body = '发布任务运费';
        $out_trade_no = input('post.out_trade_no');
        $total_fee = input('post.total_fee');
        $spbill_create_ip = getClientIP();
        $body = urldecode($body);

        $data = array(
            'body' => $body,
            'out_trade_no' => $out_trade_no,
            'total_fee' => $total_fee,
            'spbill_create_ip' => $spbill_create_ip,
        );

        $encpt = WeEncryption::getInstance();
        $url = config('we_notify_url');
        $encpt->setNotifyUrl($url);

        $curl = new Curl();
        $xmlData = $encpt->sendRequest($curl, $data);

        $postObj = $encpt->xmlToObject($xmlData);
        if (false === $postObj) {
            exit;
        }

        if ('FAIL' == $postObj->return_code) {
            echo $postObj->return_msg;
        } else {
            $resignData = array(
                'appid' => $postObj->appid,
                'partnerid' => $postObj->mch_id,
                'prepayid' => $postObj->prepay_id,
                'noncestr' => $postObj->nonce_str,
                'timestamp' => time(),
                'package' => 'Sign=WXPay'
            );
            $sign = $encpt->getClientPay($resignData);
            $resignData['sign'] = $sign;
            echo json_encode($resignData);
        }
    }

    /**
     * 微信支付统一下单接口
     * @return bool
     */
    public function alipay()
    {
        // 获取传递数据
        $subject = input('post.subject');
        $total_amount = input('post.total_amount');

        if (empty($subject) && empty($total_amount)) {
            exit;
        }

        // 将 subject 进行编码，防止中文出错
        $subject = urldecode($subject);

        $time = microtime(true);
        $time = explode(".", $time);
        $out_trade_no = date('YmdHi', time()) . $time[1];

        // 业务参数数组
        $bizContent = array(
            "timeout_express" => "30m",
            "product_code" => "QUICK_MSECURITY_PAY",
            "total_amount" => $total_amount,
            "subject" => $subject,
            "out_trade_no" => $out_trade_no
        );
        $bizContent = json_encode($bizContent);
        // 公共参数数组
        $sParam = array(
            'app_id' => config('app_id'),
            'method' => 'alipay.trade.app.pay',
            'charset' => 'utf-8',
            'sign_type' => 'RSA',
            'sign' => '',
            'timestamp' => date("Y-m-d H:i:s", time()),
            'version' => '1.0',
            'notify_url' => config('ali_notify_url'),
            'biz_content' => $bizContent
        );

        $encpt = new Encryption();    // 实例化支付宝支付类
        /** 设置私钥 */
        $encpt->setRsaPriKeyFile(config('private_key'));
        /** 获取签名 */
        $curl = new Curl();
        $content = $encpt->requestAlipay($sParam, $curl);
        echo $content;
    }

    /**
     * 获取账户对应商户余额
     * @return \think\response\Json
     */
    public function getUserAccountMoney()
    {
        $userId = input('user_id');

        if (false == is_numeric($userId) || $userId <= 0) {
            $this->ajaxMsg['msg'] = "用户参数错误";
            return json($this->ajaxMsg);
        }

        $userAccountModel = new UserAccount();
        if ($userAccountModel->isExistsById($userId)) {

            $merchantLogic = new MerchantLogic();
            $merchantInfo = $merchantLogic->isMerchantByUserId($userId);
            if(false === $merchantInfo){
                $this->ajaxMsg['msg'] = "用户参数错误";
                return json($this->ajaxMsg);
            }

            $userMoneyAccountLogic = new UserMoneyAccount();
            $this->ajaxMsg['resultDao'] = ['money' => $userMoneyAccountLogic->getAccountMoney($userId)];
            $this->ajaxMsg['code'] = self::RESULT_SUCCESS;
            $this->ajaxMsg['msg'] = "查询成功";
        } else {
            $this->ajaxMsg['code'] = self::RESULT_ERROR;
            $this->ajaxMsg['msg'] = "用户不存在";
        }

        return json($this->ajaxMsg);
    }

    /**
     * 获取账户余额
     * @return \think\response\Json
     */
    private function getUserAccountMoneyBak()
    {
        $userId = input('user_id');

        if (false == is_numeric($userId) || $userId <= 0) {
            $this->ajaxMsg['msg'] = "用户参数错误";
            return json($this->ajaxMsg);
        }

        $userAccountModel = new UserAccount();
        if ($userAccountModel->isExistsById($userId)) {
            $userMoneyAccountLogic = new UserMoneyAccount();
            $this->ajaxMsg['resultDao'] = ['money' => $userMoneyAccountLogic->getAccountMoney($userId)];
            $this->ajaxMsg['code'] = self::RESULT_SUCCESS;
            $this->ajaxMsg['msg'] = "查询成功";
        } else {
            $this->ajaxMsg['code'] = self::RESULT_ERROR;
            $this->ajaxMsg['msg'] = "用户不存在";
        }

        return json($this->ajaxMsg);
    }

    /**
     * 余额支付
     * @return \think\response\Json
     */
    public function payByAccountMoney()
    {
        $userId = input('post.user_id');
        $payNo = input('post.pay_no');
        //  $payPassWord = input('post.pay_word');
        $payMoney = input('post.money');

        if (false == is_numeric($userId) || $userId <= 0) {
            $this->ajaxMsg['msg'] = "用户参数错误";
            return json($this->ajaxMsg);
        }

        $userAccountModel = new UserAccount();
        if ($userAccountModel->isExistsById($userId)) {
            //判断他是不是店员或者店主
            $merchantModel = new MerchantInfo();
            $employeeModel = new MerchantEmployee();
            $merchantInfo = $employeeInfo = array();
            if (($merchantInfo = $merchantModel->isMerchant($userId)) || ($employeeInfo = $employeeModel->isEmployee($userId))) {
                $merchantId = $merchantInfo?$merchantInfo['id']:$employeeInfo['mc_id'];
                $userMoneyAccountLogic = new MerchantMoneyLogic();
                $task = new Task();
                $info = $task->getSourceByPayNo($payNo);
                if(!$info){
                    $this->ajaxMsg['msg'] = "订单信息错误";
                    return json($this->ajaxMsg);
                }
                $needPay = $task->getWaitPayMoneybyPayNo($payNo);
                if ($needPay * 100 == $payMoney * 100) {
                    if ($userMoneyAccountLogic->getAccountMoney($merchantId) >= $payMoney) {
                        Db::startTrans();
                        try {
                            $userMoneyAccountLogic->payByAccount($merchantId, $payNo, $payMoney);
                            $task->setStatusPublish($payNo);
                            if($info['out_trade_num'] && $info['source']){
                                $order = new OtherOrder();
                                $order->save(['publish_date'=>time()], ['order_id'=>$info['out_trade_num']]);
                            }
                            $this->ajaxMsg['code'] = self::RESULT_SUCCESS;
                            $this->ajaxMsg['msg'] = "支付成功";
                            // 提交事务
                            Db::commit();
                        } catch (\Exception $e) {
                            // 回滚事务
                            $this->ajaxMsg['code'] = self::RESULT_ERROR;
                            $this->ajaxMsg['msg'] = "支付失败";
                            Db::rollback();
                        }
                    } else {
                        $this->ajaxMsg['code'] = self::RESULT_ERROR;
                        $this->ajaxMsg['msg'] = "余额不足，请充值";
                    }
                } else {
                    $this->ajaxMsg['code'] = self::RESULT_ERROR;
                    $this->ajaxMsg['msg'] = "支付金额不正确";
                }
            } else {
                $this->ajaxMsg['code'] = self::RESULT_ERROR;
                $this->ajaxMsg['msg'] = "用户权限不足";
            }
        } else {
            $this->ajaxMsg['code'] = self::RESULT_ERROR;
            $this->ajaxMsg['msg'] = "用户不存在";
        }
        return json($this->ajaxMsg);
    }


    /**
     * 取消任务的规则：只有待接单状态下能取消订单
     */
    public function cancelTask()
    {
        $userId = input('post.user_id');
        $taskId = input('post.id');

        if (false == is_numeric($userId) || $userId <= 0) {
            $this->ajaxMsg['msg'] = "用户参数错误";
            return json($this->ajaxMsg);
        }

        $merchantModel = new MerchantInfo();
        $employeeModel = new MerchantEmployee();
        $taskModel = new Task();

        //校验用户身份和任务状态
        if ($merchantModel->isMerchant($userId) || $employeeModel->isEmployee($userId)) {
            if ($detail = $taskModel->isNoApply($taskId, $userId)) {
                $userMoneyAccountLogic = new UserMoneyAccount();
                Db::startTrans();
                try {
                    $taskModel->setStatusCancel($detail['id']);
                    $userMoneyAccountLogic->refund($userId, $detail['pay_no'], $taskId, $detail['price'] + $detail['extra_cost']);
                    $this->ajaxMsg['code'] = self::RESULT_SUCCESS;
                    $this->ajaxMsg['msg'] = "取消成功";
                    // 提交事务
                    Db::commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    $this->ajaxMsg['code'] = self::RESULT_ERROR;
                    $this->ajaxMsg['msg'] = "取消失败，请稍后重试！";
                    Db::rollback();
                }
            } else {
                $this->ajaxMsg['code'] = self::RESULT_ERROR;
                $this->ajaxMsg['msg'] = "数据错误";
            }
        } else {
            $this->ajaxMsg['code'] = self::RESULT_ERROR;
            $this->ajaxMsg['msg'] = "用户错误";
        }
        return json($this->ajaxMsg);
    }

    /**
     * 任务完成
     * @return \think\response\Json
     */
    public function setTaskFinish()
    {
        $userId = input('post.user_id');
        $taskId = input('post.id');

        if (false == is_numeric($userId) || $userId <= 0) {
            $this->ajaxMsg['msg'] = "用户参数错误";
            return json($this->ajaxMsg);
        }

        $merchantModel = new MerchantInfo();
        $employeeModel = new MerchantEmployee();
        $taskModel = new Task();

        //校验用户身份和任务状态
        if ($merchantModel->isMerchant($userId) || $employeeModel->isEmployee($userId)) {
            if (!$detail = $taskModel->isNoApply($taskId, $userId)) {
                if($taskModel->setStatusFinished($detail['id'])){
                    $this->ajaxMsg['code'] = self::RESULT_SUCCESS;
                    $this->ajaxMsg['msg'] = "设置成功";
                }else{
                    $this->ajaxMsg['code'] = self::RESULT_ERROR;
                    $this->ajaxMsg['msg'] = "设置失败，请稍后重试！";
                }
            } else {
                $this->ajaxMsg['code'] = self::RESULT_ERROR;
                $this->ajaxMsg['msg'] = "数据错误";
            }
        } else {
            $this->ajaxMsg['code'] = self::RESULT_ERROR;
            $this->ajaxMsg['msg'] = "用户错误";
        }
        return json($this->ajaxMsg);
    }


    /**
     * 获取token
     * @return string
     */
    public function getQiniuToken()
    {
        return $this->qiniuToken();
    }


    /**
     * 根据用户id获取商户信息
     * @return \think\response\Json
     */
    public function getMerchantInfo()
    {
        $userId = input('user_id');
        $userType = input('type');

        if (false == is_numeric($userId) || $userId <= 0 || false == is_numeric($userType) || $userType < 0) {
            $this->ajaxMsg['msg'] = "用户参数错误";
            return json($this->ajaxMsg);
        }

        $info = array();
        $merChantModel = new MerchantLogic();
        if (self::MERCHANT == $userType) {
            $info = $merChantModel->getInfoByUserId($userId);
        } elseif (self::EMPLOYEE == $userType) {
            $employeeModel = new MerchantEmployee();
            if($employeeInfo = $employeeModel->isEmployee($userId)){
                $info = $merChantModel->isMerchantById($employeeInfo['mc_id']);
            }

        }
        if ($info) {
            $this->ajaxMsg['code'] = self::RESULT_SUCCESS;
            $this->ajaxMsg['msg'] = "查询成功";
            $this->ajaxMsg['resultDao'] = $info;
        } else {
            $this->ajaxMsg['code'] = self::RESULT_ERROR;
            $this->ajaxMsg['msg'] = "数据为空";
        }
        return json($this->ajaxMsg);

    }

    /**
     * 获取版本更新信息
     * @return \think\response\Json
     */
    public function getUpdateInfo()
    {
        return $this->getUpdate();
    }


    /**
     * 修改登录密码
     * updatePass
     * @return \think\response\Json
     */
    public function updatePass(){
        $mobile = input('post.mobile');
        $login_pass = input('post.login_pass');
        $code = input('post.code');
        return parent::setPass($mobile, $login_pass, $code);
    }

    /**
     * 获取超送费
     * @return \think\response\Json
     */
    public function getTransFee()
    {
        $merchantId = input('merchantId');
        $address = input('address');

        $merchantModel = new MerchantInfo();
        $merchantInfo = $merchantModel->isMerchantById($merchantId);
        if(false == $merchantInfo){
            $this->ajaxMsg['msg'] = "商户暂未通过审核";
            return json($this->ajaxMsg);
        }
        $userAccount = new User();
        $userInfo = $userAccount->dealUserInfo($merchantInfo['mc_tb_user_id']);
        if(false == $userInfo){
            $this->ajaxMsg['msg'] = "商户用户信息不存在";
            return json($this->ajaxMsg);
        }
        if(!($userInfo['area_id'] && $userInfo['city_id'])){
            $this->ajaxMsg['msg'] = "商户地区信息不完整";
            return json($this->ajaxMsg);
        }

        $merchantLogic = new MerchantLogic();
        $data = $merchantLogic->getDistanceFee($merchantInfo, $userInfo, $address);
        if(1 == $data['code']){
            $this->ajaxMsg['code'] = self::RESULT_SUCCESS;
            $this->ajaxMsg['resultDao'] = $data['data'];
        }
        $this->ajaxMsg['msg'] = $data['msg'];
        return json($this->ajaxMsg);
    }


    /**
     * 更新用户信息
     * @return \think\response\Json
     */
    public function updateLocationInfo()
    {
        $code = input('post.mc_invite_code');
        $userId = input('post.user_id');
        if((false == is_numeric($userId)) || $userId<1 || (false == is_numeric($code)) || $code<1  ){
            $this->ajaxMsg['msg'] = '用户参数错误';
            $this->ajaxMsg['code'] = self::RESULT_PARAMETERS_DEFECT;
            return json($this->ajaxMsg);
        }
        $userLogic = new User();
        $userInfo = $userLogic->dealUserInfo($userId);
        if(!$userInfo){
            $this->ajaxMsg['msg'] = '用户不存在';
            return json($this->ajaxMsg);
        }
        $merchantModel = new MerchantInfo();
        $type = self::NOMAL;
        if($info = $merchantModel->isExistCode($code)){
            //添加数据到员工表
            $employee = new MerchantEmployee();
            $data = [
                'mc_id' => $info['id'],
                'mobile' => $userInfo['mobile'],
                'user_id' => $userId,
                'create_date' => date('Y-m-d H:i:s'),
            ];
            if($flag = $employee->addEmployee($data, self::VERSION)){
                $type = self::EMPLOYEE;
                $this->ajaxMsg['resultDao'] = ['type'=>$type];
                $this->ajaxMsg['code'] = self::RESULT_SUCCESS;
                $this->ajaxMsg['msg'] = '操作成功';
            }else{
                $this->ajaxMsg['code'] = self::RESULT_STATUS_EXCEPTION;
                $this->ajaxMsg['msg'] = '操作失败';
            }
        }else{
            $this->ajaxMsg['resultDao'] = ['type'=>$type];
            $this->ajaxMsg['msg'] = '邀请码错误';
        }
        return json($this->ajaxMsg);
    }
}
