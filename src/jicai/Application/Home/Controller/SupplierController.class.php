<?php
namespace Home\Controller;
use Think\Controller;
class SupplierController extends AuthController {
    
       public function supplierlist() {


        $this->display('supplier/list');
    }
}