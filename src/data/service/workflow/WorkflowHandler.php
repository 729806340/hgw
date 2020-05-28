<?php

/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/11/2
 * Time: 10:52
 */
abstract class WorkflowHandler
{
    protected $_config;

    protected $_attributes;

    abstract public function getId();

    abstract public function getConfig();

    public function getTitle($id = null)
    {
        if ($this->_config === null) $this->_config = $this->getConfig();
        if (!isset($this->_config['name'])) throw new Exception('处理器模型名称配置项未设置');
        if (null !== $id) {
            /** @var goodsModel $goodsModel */
            $goodsModel = Model('goods');
            if ($this->_config['model'] == 'goods') {
                $model = $goodsModel->getGoodsInfoByID($id);
                if (!empty($model)) return $this->_config['name'] . '(' . $id . ':' . $model['goods_name'] . ')';
            } elseif ($this->_config['model'] == 'goods_common') {
                $model = $goodsModel->getGoodsCommonInfoByID($id);
                if (!empty($model)) return $this->_config['name'] . '(' . $id . ':' . $model['goods_name'] . ')';
            }
        }
        return $id === null ? $this->_config['name'] : $this->_config['name'] . '(ID:' . $id . ')';
    }

    public function getDescription()
    {
        if ($this->_config === null) $this->_config = $this->getConfig();
        if (!isset($this->_config['description'])) return '';
        return $this->_config['description'];
    }


    public function getAction($group)
    {
        if ($this->_config === null) $this->_config = $this->getConfig();
        $groupConfig = $this->getGroupConfig($group);
        return isset($groupConfig['action']) ? $groupConfig['action'] : (isset($this->_config['action']) ? $this->_config['action'] : '');
    }

    public function getReference($id = null)
    {
        if ($this->_config === null) $this->_config = $this->getConfig();
        if (!isset($this->_config['reference'])) return '';
        return $id === null ? $this->_config['reference'] : str_replace('{id}', $id, $this->_config['reference']);
    }

    public function getModelName()
    {
        if ($this->_config === null) $this->_config = $this->getConfig();
        if (!isset($this->_config['model'])) throw new Exception('处理器模型名称配置项未设置');
        return $this->_config['model'];
    }

    public function getAttributes()
    {
        if ($this->_attributes !== null) return $this->_attributes;
        $config = $this->getConfig();
        if (!isset($config['attributes']) || empty($config['attributes'])) {
            throw new Exception('配置信息有误，请检查配置代码！');
        }
        $attributes = $config['attributes'];
        if (is_string($attributes)) {
            return $this->_attributes = array(array('label' => ucfirst($attributes), 'name' => $attributes, 'type' => 'text'));
        } else if (is_array($attributes)) {
            if (isset($attributes[0]) && is_array($attributes[0])) return $this->_attributes = $attributes;
            elseif (isset($attributes['name']) && is_string($attributes['name'])) return $this->_attributes = array($attributes);
        }
        return $this->_attributes = array();
    }


    /**
     * 获取启动用户组
     * @return mixed
     * @throws Exception
     */
    public function getStartGroup()
    {
        if ($this->_config === null) $this->_config = $this->getConfig();
        if (!isset($this->_config['start']) || empty($this->_config['start'])) {
            throw new Exception('处理器模型起始用户组未配置！');
        }
        return $this->_config['start'];

    }

    /**
     * 获取指定/当前用户组工作流配置
     * @param string $group
     * @return mixed
     * @throws Exception
     */
    public function getGroupConfig($group)
    {
        if ($group === null) throw new Exception('Group参数不得为空！');
        if ($this->_config === null) $this->_config = $this->getConfig();
        if (!isset($this->_config['flow']) || !isset($this->_config['flow'][$group]) || empty($this->_config['flow'][$group])) {
            throw new Exception('指定用户组【' . $group . '】无权没有审批操作权限！');
        }
        return $this->_config['flow'][$group];
    }


    /**
     * 获取批准配置
     * @param string $group
     * @return string
     */
    public function getApproveConfig($group)
    {
        $groupConfig = $this->getGroupConfig($group);
        if (!isset($groupConfig['approve']) || empty($groupConfig['approve'])) {
            return '';
        }
        return $groupConfig['approve'];
    }

    /**
     * 获取拒绝配置
     * @param string $group
     * @return string
     */
    public function getRejectConfig($group)
    {
        $groupConfig = $this->getGroupConfig($group);
        if (!isset($groupConfig['reject']) || empty($groupConfig['reject'])) {
            return '';
        }
        return $groupConfig['reject'];
    }


    /**
     * @param $post array
     * @param $service WorkflowService
     * @return bool|string|array
     */
    public function response($post, $service)
    {
        // 子方法根据自己的控制字段处理是否通过审核，并将$post['opinion']置为对应值
        /** 示例
         * $attributes = $this->getAttributes();
         * foreach ($attributes as $attribute){
         * if(isset($attribute['on'])&&!in_array($service->getGroup(),(array)$attribute['on'])) continue;
         * if(isset($attribute['mod'])&&$attribute['mod']=='control'&&isset($post[$attribute['name']])){
         * $post['opinion'] = $post[$attribute['name']]==0?1:0;
         * }
         * }
         * return parent::response($post, $service);
         */
        $opinion = intval($post['opinion']);
        return $opinion == 0 ? $this->reject($post, $service) : $this->approve($post, $service);
    }

    /**
     * @param $post array
     * @param $service WorkflowService
     * @return bool
     */
    public function approve($post, $service)
    {
        $attributes = $this->getAttributes();
        $model = $service->getModel();
        $newValue = $model['new_value'];
        $hasNewValue = false;
        $attachment = array();
        $control = null;
        foreach ($attributes as $attribute) {
            if (isset($attribute['on']) && !in_array($service->getGroup(), (array)$attribute['on'])) continue;
            if (isset($attribute['mod']) && $attribute['mod'] == 'control') {
                $control[$attribute['name']] = $post[$attribute['name']];
            } else if (isset($attribute['attachment']) && $attribute['attachment'] == true) {
                $attachment[$attribute['name']] = $post[$attribute['name']];
            } else if (isset($post[$attribute['name']]) && $post[$attribute['name']] !== null) {
                $newValue[$attribute['name']] = $post[$attribute['name']];
                $hasNewValue = true;
            }
        }
        if ($hasNewValue === true) {
            $model['new_value'] = $newValue;
            $service->workflowModel->editWorkflow(array('new_value' => $newValue), array('id' => $model['id']));
        }
        $message = trim($post['message']);
        return $service->approve($message, $attachment, $control);
    }

    /**
     * @param $post array
     * @param $service WorkflowService
     * @return bool
     */
    public function reject($post, $service)
    {

        $attributes = $this->getAttributes();
        $model = $service->getModel();
        $newValue = $model['new_value'];
        $hasNewValue = false;
        $attachment = array();
        $control = null;
        foreach ($attributes as $attribute) {
            if (isset($attribute['on']) && !in_array($service->getGroup(), (array)$attribute['on'])) continue;
            if (isset($attribute['mod']) && $attribute['mod'] == 'control') {
                $control[$attribute['name']] = $post[$attribute['name']];
            } else if (isset($attribute['attachment']) && $attribute['attachment'] == true) {
                $attachment[$attribute['name']] = $post[$attribute['name']];
            } else if (isset($post[$attribute['name']]) && $post[$attribute['name']] !== null) {
                $newValue[$attribute['name']] = $post[$attribute['name']];
                $hasNewValue = true;
            }
        }
        if ($hasNewValue === true) {
            $model['new_value'] = $newValue;
            $service->workflowModel->editWorkflow(array('new_value' => $newValue), array('id' => $model['id']));
        }
        $message = trim($post['message']);
        return $service->reject($message, $attachment, $control);
        /*try{
            $service->reject($message);
        }catch (Exception $e){
            return $e->getMessage();
        }
        return true;*/
    }


}