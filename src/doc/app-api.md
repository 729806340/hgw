# 汉购网App Api

说明：此文件用于API内部版本控制，发行版本由项目统一输出给客户端人员

## 首页与内容管理

### 获取首页数据

#### 接口功能
本接口获取首页所需数据
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | index.index

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 可选 | 登录签名 

#### 返回的数据结果集


| 字段名 |  类型 |  说明
| ---------  | -------- | ------
list | array | 首页项目列表

+ list数据类型说明

     + home1 单图片布局，此项目仅包含一张主要图片
     
     | 字段名 |  类型 |  说明
     | ---------  | -------- | ------
     title | string | 标题
     stitle | string | 副标题
     style | string | 样式名称
     lcurl | string | 图标
     image | string | 主要图片
     type | string | 链接类型，包括：1、商品，2、店铺，3专题，4链接（keyword：data数据为关键字；special：data数据为专题ID；goods：data数据为商品ID；url：data数据为完整的URL）
     data | string | data数据
     
     + home3 多图片布局，此项目仅包含多张图片
     
     | 字段名 |  类型 |  说明
     | ---------  | -------- | ------
     title | string | 标题
     stitle | string | 副标题
     style | string | 样式名称
     lcurl | string | 图标
     item | array | 图片列表，包含字段image,type,data；含义与home对应字段相同
     
     + goods 商品列表布局，此项目仅包含多个商品
     
     | 字段名 |  类型 |  说明
     | ---------  | -------- | ------
     title | string | 标题
     stitle | string | 副标题
     style | string | 样式名称
     lcurl | string | 图标
     item | array | 商品列表
     
     + goods布局item字段说明
     
     | 字段名 |  类型 |  说明
     | ---------  | -------- | ------
     goods_id | string | 商品ID
     goods_name | string | 商品名称
     goods_promotion_price | string | 商品销售价格
     goods_image | string | 商品图片
     

#### 返回正确结果示例
```json
{
    "code": "200",
    "datas": {
        "list": [
            {
                "home1": {
                    "title": "title",
                    "stitle": "subtitle",
                    "style": "style1",
                    "lcurl": "icon",
                    "image": "http://hangowa.com/upload/img.png",
                    "type": "goods",
                    "data": "1"
                }
            },
            {
                "home3": {
                    "title": "模块C",
                    "stitle": "subtitle",
                    "style": "style2",
                    "lcurl": "icon",
                    "item": [
                        {
                            "image": "http://www2.hangowa.dev.com/data/upload/mobile/special/s900000001/s900000001_05362119663088855.png",
                            "type": "url",
                            "data": "/test"
                        }
                    ]
                }
            },
            {
                "goods": {
                    "title": "聚划算",
                    "stitle": "真的聚划算",
                    "style": "style3",
                    "lcurl": "",
                    "item": [
                        {
                            "goods_id": "101985",
                            "goods_name": "酒立得 暧昧（Ambiguous）原味伏特加 700ml* 单瓶",
                            "goods_promotion_price": "78.00",
                            "goods_image": "http://www2.hangowa.dev.com/data/upload/shop/common/default_goods_image_240.gif"
                        },
                        {
                            "goods_id": "101915",
                            "goods_name": "HOLA赫拉牛奶雪肤焕白洁面乳170ml 深层温和清洁滋润补水保湿提亮",
                            "goods_promotion_price": "49.00",
                            "goods_image": "http://www2.hangowa.dev.com/data/upload/shop/common/default_goods_image_240.gif"
                        }
                    ]
                }
            }
        ],
        "special_id": "0"
    }
}
```
#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```

### 获取首页数据（简版）

#### 接口功能
本接口获取首页所需简要数据
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | index.index2

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 可选 | 登录签名 

#### 返回的数据结果集


| 字段名 |  类型 |  说明
| ---------  | -------- | ------
banner | object | 首页顶部banner列表
goods | object | 首页商品推荐列表
special | array | 首页专题列表


 + banner 多图片，此项目仅包含多张图片
 
 | 字段名 |  类型 |  说明
 | ---------  | -------- | ------
 title | string | 标题
 stitle | string | 副标题
 style | string | 样式名称
 lcurl | string | 图标
 item | array | 图片列表，包含字段image,type,data；含义与home对应字段相同
 
 
 
 
 + goods 商品列表，此项目仅包含多个商品
 
 | 字段名 |  类型 |  说明
 | ---------  | -------- | ------
 title | string | 标题
 stitle | string | 副标题
 style | string | 样式名称
 lcurl | string | 图标
 item | array | 商品列表
 
 + goods布局item字段说明
 
 | 字段名 |  类型 |  说明
 | ---------  | -------- | ------
 goods_id | string | 商品ID
 goods_name | string | 商品名称
 goods_promotion_price | string | 商品销售价格
 goods_image | string | 商品图片
     
 
 + special 数组元素说明
 
 | 字段名 |  类型 |  说明
 | ---------  | -------- | ------
 title | string | 标题
 stitle | string | 副标题
 style | string | 样式名称
 lcurl | string | 图标
 image | string | 主要图片
 type | string | 链接类型，包括keyword：data数据为关键字；special：data数据为专题ID；goods：data数据为商品ID；url：data数据为完整的URL
 data | string | data数据
 

#### 返回正确结果示例
```json
{
    "code":"200",
    "datas":{
        "special":[
            {
                "title":"title",
                "stitle":"subtitle",
                "style":"abc",
                "lcurl":"icon",
                "image":"http://www2.hangowa.dev.com/data/upload/mobile/special/s900000001/s900000001_05362120377949610.png",
                "type":"goods",
                "data":"1"
            }
        ],
        "banner":{
            "title":"模块C",
            "stitle":"subtitle",
            "style":"style1",
            "lcurl":"icon",
            "item":[
                {
                    "image":"http://www2.hangowa.dev.com/data/upload/mobile/special/s900000001/s900000001_05362119663088855.png",
                    "type":"url",
                    "data":"/test"
                }
            ]
        },
        "goods":{
            "title":"聚划算",
            "stitle":"真的聚划算",
            "style":"aa",
            "lcurl":"",
            "item":[
                {
                    "goods_id":"101985",
                    "goods_name":"酒立得 暧昧（Ambiguous）原味伏特加 700ml* 单瓶",
                    "goods_promotion_price":"78.00",
                    "goods_image":"http://www2.hangowa.dev.com/data/upload/shop/common/default_goods_image_240.gif"
                },
                {
                    "goods_id":"101915",
                    "goods_name":"HOLA赫拉牛奶雪肤焕白洁面乳170ml 深层温和清洁滋润补水保湿提亮",
                    "goods_promotion_price":"49.00",
                    "goods_image":"http://www2.hangowa.dev.com/data/upload/shop/common/default_goods_image_240.gif"
                }
            ]
        }
    }
}
```
#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```


### 获取默认搜索数据

#### 接口功能
本接口获取默认搜索所需数据
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | index.search_key

#### 应用级参数

无

#### 返回的数据结果集


| 字段名 |  类型 |  说明
| ---------  | -------- | ------
list | array | 默认搜索关键字列表
his_list | array | 搜索历史列表（服务端返回搜索历史需要cookie支持，不支持请使用客户端存储历史纪录）

#### 返回正确结果示例
```json
{"code":"200","datas":{"list":["花牛苹果","蜜柚","桔子","九月红","柿子","大闸蟹","鸡蛋","大米","油","劲酒","周黑鸭"],"his_list":[]}}
```
#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```


### 获取热门搜索数据

#### 接口功能
本接口获取热门搜索所需数据
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | index.search_hot

#### 应用级参数

无

#### 返回的数据结果集

array

#### 返回正确结果示例
```json
{"code":"200","datas":["童装","劲酒"]}
```
#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```

### 获取分类首页数据（简版）

#### 接口功能
本接口获取分类首页所需简要数据
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | index.category2

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 可选 | 登录签名 

#### 返回的数据结果集


| 字段名 |  类型 |  说明
| ---------  | -------- | ------
function | array | 分类首页功能列表
special | array | 分类首页专题列表
 
 + special / function数组元素字段说明
 
 | 字段名 |  类型 |  说明
 | ---------  | -------- | ------
 title | string | 标题
 stitle | string | 副标题
 style | string | 样式名称
 lcurl | string | 图标
 image | string | 主要图片
 type | string | 链接类型，包括keyword：data数据为关键字；special：data数据为专题ID；goods：data数据为商品ID；url：data数据为完整的URL
 data | string | data数据
 

#### 返回正确结果示例
```json
{
    "code":"200",
    "datas":{
        "special":{
            "title":"分类推荐",
            "stitle":"",
            "style":"",
            "lcurl":"",
            "item":[
                {
                    "image":"http://www3.hangowa.com/data/upload/mobile/special/s900000002/s900000002_05385683095005805.jpg",
                    "type":"special",
                    "data":"1",
                    "titlebelow":""
                },
                {
                    "image":"http://www3.hangowa.com/data/upload/mobile/special/s900000002/s900000002_05385683480037703.jpg",
                    "type":"special",
                    "data":"2",
                    "titlebelow":""
                },
                {
                    "image":"http://www3.hangowa.com/data/upload/mobile/special/s900000002/s900000002_05385683653347861.jpg",
                    "type":"special",
                    "data":"2",
                    "titlebelow":""
                },
                {
                    "image":"http://www3.hangowa.com/data/upload/mobile/special/s900000002/s900000002_05385683799325613.jpg",
                    "type":"special",
                    "data":"3",
                    "titlebelow":""
                }
            ]
        }
    }
}
```
#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```




### 获取通用专题数据

#### 接口功能
本接口获取通用专题数据
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | index.special

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 可选 | 登录签名 
special_id | string | 必须 | 专题ID 

#### 返回的数据结果集


| 字段名 |  类型 |  说明
| ---------  | -------- | ------
special_desc | string | 专题描述
special_id | string | 专题ID
list | array | 专题项目列表


#### 返回正确结果示例
```json
{
    "code": "200",
    "datas": {
        "special_desc": "汉购超级年货节",
        "list": [
            {
                "home3": {
                    "title": "",
                    "item": [
                        {
                            "image": "http://www2.hangowa.dev.com/data/upload/shop/common/default_goods_image_240.gif",
                            "type": "url",
                            "data": "http://www.hangowa.com/?act=special&amp;op=show&amp;special_id=56",
                            "titlebelow": ""
                        },
                        {
                            "image": "http://www2.hangowa.dev.com/data/upload/shop/common/default_goods_image_240.gif",
                            "type": "url",
                            "data": "http://www.hangowa.com/?act=special&amp;op=show&amp;special_id=57",
                            "titlebelow": ""
                        },
                        {
                            "image": "http://www2.hangowa.dev.com/data/upload/shop/common/default_goods_image_240.gif",
                            "type": "url",
                            "data": "http://www.hangowa.com/?act=special&amp;op=show&amp;special_id=58",
                            "titlebelow": ""
                        },
                        {
                            "image": "http://www2.hangowa.dev.com/data/upload/shop/common/default_goods_image_240.gif",
                            "type": "url",
                            "data": "http://www.hangowa.com/?act=special&amp;op=show&amp;special_id=59",
                            "titlebelow": ""
                        }
                    ]
                }
            },
            {
                "home1": {
                    "title": "",
                    "image": "http://www2.hangowa.dev.com/data/upload/shop/common/default_goods_image_240.gif",
                    "type": "",
                    "data": ""
                }
            },
            {
                "goods": {
                    "title": "",
                    "item": [
                        {
                            "goods_id": "101613",
                            "goods_name": "农家自制 天然富硒手工豆皮 头层豆腐皮500g  恩施田土王精制豆皮 包邮",
                            "goods_promotion_price": "11.90",
                            "goods_image": "http://www2.hangowa.dev.com/data/upload/shop/common/default_goods_image_240.gif"
                        },
                        {
                            "goods_id": "101273",
                            "goods_name": "洋溪蜜柚2个装 共6斤左右 新鲜时令水果",
                            "goods_promotion_price": "16.00",
                            "goods_image": "http://www2.hangowa.dev.com/data/upload/shop/common/default_goods_image_240.gif"
                        }
                    ]
                }
            }
        ],
        "special_id": "21"
    }
}
```
#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```




### 获取食话首页数据

#### 接口功能
本接口获取食话首页数据
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | index.shihua

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 可选 | 登录签名 


#### 返回的数据结果集


| 字段名 |  类型 |  说明
| ---------  | -------- | ------
list | array | 食话首页项目列表

+ list数据类型说明

     + home1 单图片布局，此项目仅包含一张主要图片（同首页）
     
     + home3 多图片布局，此项目仅包含多张图片（同首页）
     
     + article 文章列表布局，此项目仅包含多个文章
     
     | 字段名 |  类型 |  说明
     | ---------  | -------- | ------
     title | string | 标题
     stitle | string | 副标题
     style | string | 样式名称
     lcurl | string | 图标
     item | array | 文章列表
     
     + article布局item字段说明
     
     | 字段名 |  类型 |  说明
     | ---------  | -------- | ------
     article_id | string | 文章ID
     article_title | string | 文章名称
     article_author | string | 文章作者
     image | string | 文章图片
     

#### 返回正确结果示例
```json
{
    "code": "200",
    "datas": {
        "list": [
            {
                "home1": {
                    "title": "title",
                    "stitle": "subtitle",
                    "style": "style1",
                    "lcurl": "icon",
                    "image": "http://hangowa.com/upload/img.png",
                    "type": "goods",
                    "data": "1"
                }
            },
            {
                "home3": {
                    "title": "模块C",
                    "stitle": "subtitle",
                    "style": "style2",
                    "lcurl": "icon",
                    "item": [
                        {
                            "image": "http://www2.hangowa.dev.com/data/upload/mobile/special/s900000001/s900000001_05362119663088855.png",
                            "type": "url",
                            "data": "/test"
                        }
                    ]
                }
            },
            {
                "article":{
                    "title":"aaa",
                    "stitle":"aa",
                    "style":"",
                    "lcurl":"",
                    "item":[
                        {
                            "article_id":"1",
                            "article_title":"aaa1",
                            "article_class_id":"0",
                            "article_origin":"",
                            "article_origin_address":"",
                            "article_author":"aaaaa",
                            "article_abstract":"",
                            "article_content":"",
                            "article_image":"",
                            "article_keyword":"",
                            "article_link":"",
                            "article_goods":"",
                            "article_start_time":"0",
                            "article_end_time":"0",
                            "article_publish_time":"0",
                            "article_click":"0",
                            "article_sort":"0",
                            "article_commend_flag":"0",
                            "article_comment_flag":"1",
                            "article_verify_admin":"",
                            "article_verify_time":"0",
                            "article_state":"1",
                            "article_publisher_name":"aaaa",
                            "article_publisher_id":"1",
                            "article_type":"1",
                            "article_attachment_path":"/aaa",
                            "article_image_all":"/aaa/",
                            "article_modify_time":"0",
                            "article_tag":"",
                            "article_comment_count":"0",
                            "article_attitude_1":"0",
                            "article_attitude_2":"0",
                            "article_attitude_3":"0",
                            "article_attitude_4":"0",
                            "article_attitude_5":"0",
                            "article_attitude_6":"0",
                            "article_title_short":"",
                            "article_attitude_flag":"1",
                            "article_commend_image_flag":"0",
                            "article_share_count":"0",
                            "article_verify_reason":"",
                            "image":"http://www2.hangowa.dev.com/data/upload/shop/common/default_goods_image_240.gif"
                        },
                        {
                            "article_id":"2",
                            "article_title":"aaa2",
                            "article_class_id":"0",
                            "article_origin":"",
                            "article_origin_address":"",
                            "article_author":"aaaaa",
                            "article_abstract":"",
                            "article_content":"",
                            "article_image":"",
                            "article_keyword":"",
                            "article_link":"",
                            "article_goods":"",
                            "article_start_time":"0",
                            "article_end_time":"0",
                            "article_publish_time":"0",
                            "article_click":"0",
                            "article_sort":"0",
                            "article_commend_flag":"0",
                            "article_comment_flag":"1",
                            "article_verify_admin":"",
                            "article_verify_time":"0",
                            "article_state":"1",
                            "article_publisher_name":"aaaa",
                            "article_publisher_id":"1",
                            "article_type":"1",
                            "article_attachment_path":"/aaa",
                            "article_image_all":"/aaa/",
                            "article_modify_time":"0",
                            "article_tag":"",
                            "article_comment_count":"0",
                            "article_attitude_1":"0",
                            "article_attitude_2":"0",
                            "article_attitude_3":"0",
                            "article_attitude_4":"0",
                            "article_attitude_5":"0",
                            "article_attitude_6":"0",
                            "article_title_short":"",
                            "article_attitude_flag":"1",
                            "article_commend_image_flag":"0",
                            "article_share_count":"0",
                            "article_verify_reason":"",
                            "image":"http://www2.hangowa.dev.com/data/upload/shop/common/default_goods_image_240.gif"
                        },
                        {
                            "article_id":"3",
                            "article_title":"aaa3",
                            "article_class_id":"0",
                            "article_origin":"",
                            "article_origin_address":"",
                            "article_author":"aaaaa",
                            "article_abstract":"",
                            "article_content":"",
                            "article_image":"",
                            "article_keyword":"",
                            "article_link":"",
                            "article_goods":"",
                            "article_start_time":"0",
                            "article_end_time":"0",
                            "article_publish_time":"0",
                            "article_click":"0",
                            "article_sort":"0",
                            "article_commend_flag":"0",
                            "article_comment_flag":"1",
                            "article_verify_admin":"",
                            "article_verify_time":"0",
                            "article_state":"1",
                            "article_publisher_name":"aaaa",
                            "article_publisher_id":"1",
                            "article_type":"1",
                            "article_attachment_path":"/aaa",
                            "article_image_all":"/aaa/",
                            "article_modify_time":"0",
                            "article_tag":"",
                            "article_comment_count":"0",
                            "article_attitude_1":"0",
                            "article_attitude_2":"0",
                            "article_attitude_3":"0",
                            "article_attitude_4":"0",
                            "article_attitude_5":"0",
                            "article_attitude_6":"0",
                            "article_title_short":"",
                            "article_attitude_flag":"1",
                            "article_commend_image_flag":"0",
                            "article_share_count":"0",
                            "article_verify_reason":"",
                            "image":"http://www2.hangowa.dev.com/data/upload/shop/common/default_goods_image_240.gif"
                        },
                        {
                            "article_id":"4",
                            "article_title":"aaa4",
                            "article_class_id":"0",
                            "article_origin":"",
                            "article_origin_address":"",
                            "article_author":"aaaaa",
                            "article_abstract":"",
                            "article_content":"",
                            "article_image":"",
                            "article_keyword":"",
                            "article_link":"",
                            "article_goods":"",
                            "article_start_time":"0",
                            "article_end_time":"0",
                            "article_publish_time":"0",
                            "article_click":"0",
                            "article_sort":"0",
                            "article_commend_flag":"0",
                            "article_comment_flag":"1",
                            "article_verify_admin":"",
                            "article_verify_time":"0",
                            "article_state":"1",
                            "article_publisher_name":"aaaa",
                            "article_publisher_id":"1",
                            "article_type":"1",
                            "article_attachment_path":"/aaa",
                            "article_image_all":"/aaa/",
                            "article_modify_time":"0",
                            "article_tag":"",
                            "article_comment_count":"0",
                            "article_attitude_1":"0",
                            "article_attitude_2":"0",
                            "article_attitude_3":"0",
                            "article_attitude_4":"0",
                            "article_attitude_5":"0",
                            "article_attitude_6":"0",
                            "article_title_short":"",
                            "article_attitude_flag":"1",
                            "article_commend_image_flag":"0",
                            "article_share_count":"0",
                            "article_verify_reason":"",
                            "image":"http://www2.hangowa.dev.com/data/upload/shop/common/default_goods_image_240.gif"
                        }
                    ]
                }
            }
        ],
        "special_id": "0"
    }
}
```
#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```


### 获取食旅首页数据

#### 接口功能
本接口获取食旅首页数据
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | index.shilv

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 可选 | 登录签名 


#### 返回的数据结果集


| 字段名 |  类型 |  说明
| ---------  | -------- | ------
list | array | 食旅首页项目列表（同食话）

     

#### 返回正确结果示例

同食话

#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```


### 获取值得买数据

#### 接口功能
本接口获取值得买1数据
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | index.zhidemai1

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 可选 | 登录签名 
id | string | 可选 | 可用值1值得买（默认），2聚食惠，3每日鲜，4家乡味道


#### 返回的数据结果集


| 字段名 |  类型 |  说明
| ---------  | -------- | ------
home1 | object | 顶部图片
home2 | object | 左一右二布局
goods | array | 商品列表

     

#### 返回正确结果示例

```json
{
    "code":"200",
    "datas":{
        "home1":{
            "title":"",
            "stitle":"",
            "style":"",
            "lcurl":"",
            "image":"http://www3.hangowa.com/data/upload/mobile/special/s900000006/s900000006_05403988038498777.jpg",
            "type":"keyword",
            "data":"1"
        },
        "home2":{
            "title":"",
            "square_image":"http://www3.hangowa.com/data/upload/mobile/special/s900000006/s900000006_05403179258025572.jpg",
            "square_type":"special",
            "square_data":"1",
            "rectangle1_image":"http://www3.hangowa.com/data/upload/mobile/special/s900000006/s900000006_05403179527816117.jpg",
            "rectangle1_type":"special",
            "rectangle1_data":"1",
            "rectangle2_image":"http://www3.hangowa.com/data/upload/mobile/special/s900000006/s900000006_05403179630494862.jpg",
            "rectangle2_type":"special",
            "rectangle2_data":"1"
        },
        "goods":[
            {
                "goods_id":"101355",
                "goods_name":"同船TONGCHUAN 同船山茶油750ml×2瓶（蓝色礼盒装）",
                "goods_promotion_price":"328.00",
                "goods_image":"http://www3.hangowa.com/data/upload/shop/common/default_goods_image_240.gif"
            },
            {
                "goods_id":"101354",
                "goods_name":"同船TONGCHUAN 同船野生山茶油750ml×2瓶（黄色礼盒装）",
                "goods_promotion_price":"328.00",
                "goods_image":"http://www3.hangowa.com/data/upload/shop/common/default_goods_image_240.gif"
            },
            {
                "goods_id":"101353",
                "goods_name":"同船TONGCHUAN 纯菜籽油2L×2礼盒装",
                "goods_promotion_price":"135.00",
                "goods_image":"http://www3.hangowa.com/data/upload/shop/common/default_goods_image_240.gif"
            },
            {
                "goods_id":"101352",
                "goods_name":"同船TONGCHUAN 土家土榨 物理压榨纯菜籽油 2L",
                "goods_promotion_price":"48.00",
                "goods_image":"http://www3.hangowa.com/data/upload/shop/common/default_goods_image_240.gif"
            },
            {
                "goods_id":"101350",
                "goods_name":"同船TONGCHUAN 菜籽油 家庭装5L 土家土榨 物理压榨纯菜籽油",
                "goods_promotion_price":"89.90",
                "goods_image":"http://www3.hangowa.com/data/upload/shop/common/default_goods_image_240.gif"
            }
        ]
    }
}
```

#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```



### 获取食话/食旅文章数据


#### 接口功能
本接口获取食话/食旅文章数据
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | article.view

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 可选 | 登录签名 
id | string | 必须 | 文章ID


#### 返回的数据结果集


| 字段名 |  类型 |  说明
| ---------  | -------- | ------
article_id | string | 文章ID
article_title | string | 文章标题
article_class_id | string | 文章分类ID
article_author | string | 文章来源
article_abstract | string | 文章摘要
article_content | string | 文章正文
article_image | string | 文章图片
article_keyword | string | 文章关键字
article_goods | string | 相关商品
article_start_time | string | 文章有效期开始时间
article_end_time | string | 文章有效期结束时间
article_publish_time | string | 文章发布时间
article_click | string | 文章点击量
article_sort | string | 文章排序0-255
article_commend_flag | string | 文章推荐标志0-未推荐，1-已推荐
article_comment_flag | string | 文章是否允许评论1-允许，0-不允许
article_verify_time | string | 文章审核时间
article_state | string | 1-草稿、2-待审核、3-已发布、4-回收站
article_publisher_name | string | 发布者用户名
article_publisher_id | string | 发布者编号
article_type | string | 文章类型1-管理员发布，2-用户投稿
article_attachment_path | string | 文章附件路径
article_image_all | string | 文章全部图片
article_modify_time | string | 文章修改时间
article_tag | string | 文章标签
article_comment_count | string | 文章评论数
article_title_short | string | 文章短标题
article_share_count | string | 文章分享数
     

#### 返回正确结果示例
```json
{
    "code":"200",
    "datas":{
        "article_id":"1",
        "article_title":"aaa1",
        "article_class_id":"0",
        "article_origin":"",
        "article_origin_address":"",
        "article_author":"aaaaa",
        "article_abstract":"",
        "article_content":"",
        "article_image":"aaaaa",
        "article_keyword":"",
        "article_link":"",
        "article_goods":"",
        "article_start_time":"0",
        "article_end_time":"0",
        "article_publish_time":"0",
        "article_click":"0",
        "article_sort":"0",
        "article_commend_flag":"0",
        "article_comment_flag":"1",
        "article_verify_admin":"",
        "article_verify_time":"0",
        "article_state":"1",
        "article_publisher_name":"aaaa",
        "article_publisher_id":"1",
        "article_type":"1",
        "article_attachment_path":"/aaa",
        "article_image_all":"/aaa/",
        "article_modify_time":"0",
        "article_tag":"",
        "article_comment_count":"0",
        "article_attitude_1":"0",
        "article_attitude_2":"0",
        "article_attitude_3":"0",
        "article_attitude_4":"0",
        "article_attitude_5":"0",
        "article_attitude_6":"0",
        "article_title_short":"",
        "article_attitude_flag":"1",
        "article_commend_image_flag":"0",
        "article_share_count":"0",
        "article_verify_reason":""
    }
}
```

#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```

### 获取文章评论列表数据

#### 接口功能
本接口获取文章评论列表数据
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | article.comment

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 必须 | 登录签名
 id | string | 必须 | 文章ID
 page | string | 选填 | 页码


#### 返回的数据结果集


| 字段名 |  类型 |  说明
| ---------  | -------- | ------
items | array | 评论数组
total | string | 总评论数
totalPage | string | 总页数

+ 评论数组项目说明

| 字段名 |  类型 |  说明
| ---------  | -------- | ------
comment_id | string | 评论ID
comment_type | string | 评论类型
comment_object_id | string | 评论对象ID（文章ID）
comment_message | string | 评论内容
comment_member_id | string | 评论用户ID
comment_time | string | 评论时间
comment_quote | string | 评论引用数量
comment_quotes | array | 评论引用详细
comment_up | string | 评论支持数量
     

#### 返回正确结果示例
```json
{
    "code":"200",
    "datas":{
        "items":[
            {
                "comment_id":"1",
                "comment_type":"1",
                "comment_object_id":"1",
                "comment_message":"评论内容1",
                "comment_member_id":"1",
                "comment_time":"1486522931",
                "comment_quote":"1",
                "comment_up":"100",
                "member_name":"hango",
                "member_avatar":"",
                "comment_quotes":[

                ]
            },
            {
                "comment_id":"2",
                "comment_type":"1",
                "comment_object_id":"1",
                "comment_message":"评论内容2",
                "comment_member_id":"1",
                "comment_time":"1486522931",
                "comment_quote":"1",
                "comment_up":"100",
                "member_name":"hango",
                "member_avatar":"",
                "comment_quotes":[

                ]
            },
            {
                "comment_id":"3",
                "comment_type":"1",
                "comment_object_id":"1",
                "comment_message":"评论内容3",
                "comment_member_id":"1",
                "comment_time":"1486522931",
                "comment_quote":"3",
                "comment_up":"100",
                "member_name":"hango",
                "member_avatar":"",
                "comment_quotes":[
                    {
                        "comment_id":"5",
                        "comment_type":"1",
                        "comment_object_id":"1",
                        "comment_message":"评论内容5",
                        "comment_member_id":"1",
                        "comment_time":"1486522931",
                        "comment_quote":"0",
                        "comment_up":"100",
                        "member_name":"hango",
                        "member_avatar":""
                    },
                    {
                        "comment_id":"6",
                        "comment_type":"1",
                        "comment_object_id":"1",
                        "comment_message":"评论内容6",
                        "comment_member_id":"1",
                        "comment_time":"1486522931",
                        "comment_quote":"0",
                        "comment_up":"100",
                        "member_name":"hango",
                        "member_avatar":""
                    },
                    {
                        "comment_id":"7",
                        "comment_type":"1",
                        "comment_object_id":"1",
                        "comment_message":"评论内容7",
                        "comment_member_id":"1",
                        "comment_time":"1486522931",
                        "comment_quote":"0",
                        "comment_up":"100",
                        "member_name":"hango",
                        "member_avatar":""
                    }
                ]
            },
            {
                "comment_id":"4",
                "comment_type":"1",
                "comment_object_id":"1",
                "comment_message":"评论内容4",
                "comment_member_id":"1",
                "comment_time":"1486522931",
                "comment_quote":"1",
                "comment_up":"100",
                "member_name":"hango",
                "member_avatar":"",
                "comment_quotes":[

                ]
            },
            {
                "comment_id":"5",
                "comment_type":"1",
                "comment_object_id":"1",
                "comment_message":"评论内容5",
                "comment_member_id":"1",
                "comment_time":"1486522931",
                "comment_quote":"1",
                "comment_up":"100",
                "member_name":"hango",
                "member_avatar":"",
                "comment_quotes":[

                ]
            },
            {
                "comment_id":"6",
                "comment_type":"1",
                "comment_object_id":"1",
                "comment_message":"评论内容6",
                "comment_member_id":"1",
                "comment_time":"1486522931",
                "comment_quote":"1",
                "comment_up":"100",
                "member_name":"hango",
                "member_avatar":"",
                "comment_quotes":[

                ]
            },
            {
                "comment_id":"7",
                "comment_type":"1",
                "comment_object_id":"1",
                "comment_message":"评论内容7",
                "comment_member_id":"1",
                "comment_time":"1486522931",
                "comment_quote":"1",
                "comment_up":"100",
                "member_name":"hango",
                "member_avatar":"",
                "comment_quotes":[

                ]
            },
            {
                "comment_id":"8",
                "comment_type":"1",
                "comment_object_id":"1",
                "comment_message":"评论内容8",
                "comment_member_id":"1",
                "comment_time":"1486522931",
                "comment_quote":"1",
                "comment_up":"100",
                "member_name":"hango",
                "member_avatar":"",
                "comment_quotes":[

                ]
            },
            {
                "comment_id":"9",
                "comment_type":"1",
                "comment_object_id":"1",
                "comment_message":"评论内容9",
                "comment_member_id":"1",
                "comment_time":"1486522931",
                "comment_quote":"1",
                "comment_up":"100",
                "member_name":"hango",
                "member_avatar":"",
                "comment_quotes":[

                ]
            },
            {
                "comment_id":"10",
                "comment_type":"1",
                "comment_object_id":"1",
                "comment_message":"评论内容10",
                "comment_member_id":"1",
                "comment_time":"1486522931",
                "comment_quote":"1",
                "comment_up":"100",
                "member_name":"hango",
                "member_avatar":"",
                "comment_quotes":[

                ]
            }
        ],
        "total":"18",
        "totalPage":"2"
    }
}
```


### 获取文章评论详情数据

#### 接口功能
本接口获取文章评论详情数据
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | article.comment_view

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 必须 | 登录签名
id | string | 必须 | 评论ID


#### 返回的数据结果集


+ 评论数组项目说明

| 字段名 |  类型 |  说明
| ---------  | -------- | ------
comment_id | string | 评论ID
comment_type | string | 评论类型
comment_object_id | string | 评论对象ID（文章ID）
comment_message | string | 评论内容
comment_member_id | string | 评论用户ID
comment_time | string | 评论时间
comment_quote | string | 评论引用数量
comment_quotes | array | 评论引用详细
comment_up | string | 评论支持数量
     

#### 返回正确结果示例
```json
{
    "code":"200",
    "datas":{
        "comment_id":"3",
        "comment_type":"1",
        "comment_object_id":"1",
        "comment_message":"评论内容3",
        "comment_member_id":"1",
        "comment_time":"1486522931",
        "comment_quote":"3",
        "comment_up":"101",
        "member_name":"hango",
        "member_avatar":"",
        "comment_quotes":[
            {
                "comment_id":"5",
                "comment_type":"1",
                "comment_object_id":"1",
                "comment_message":"评论内容5",
                "comment_member_id":"1",
                "comment_time":"1486522931",
                "comment_images":[
                    "http://www.hangowa.com/data/upload/shop/store/goods/87/2016/87_05328045265058323_360.jpg"
                ],
                "comment_quote":"0",
                "comment_up":"100",
                "member_name":"hango",
                "member_avatar":""
            },
            {
                "comment_id":"6",
                "comment_type":"1",
                "comment_object_id":"1",
                "comment_message":"评论内容6",
                "comment_member_id":"1",
                "comment_time":"1486522931",
                "comment_images":[
                    "http://www.hangowa.com/data/upload/shop/store/goods/87/2016/87_05328045265058323_360.jpg"
                ],
                "comment_quote":"0",
                "comment_up":"100",
                "member_name":"hango",
                "member_avatar":""
            },
            {
                "comment_id":"7",
                "comment_type":"1",
                "comment_object_id":"1",
                "comment_message":"评论内容7",
                "comment_member_id":"1",
                "comment_time":"1486522931",
                "comment_images":[
                    "http://www.hangowa.com/data/upload/shop/store/goods/87/2016/87_05328045265058323_360.jpg"
                ],
                "comment_quote":"0",
                "comment_up":"100",
                "member_name":"hango",
                "member_avatar":""
            }
        ]
    }
}
```

#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"没有找评论"}}
```


### 赞文章

#### 接口功能
本接口将文章被赞次数增加1
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | member_article.up

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 必须 | 登录签名 
id | string | 必须 | 文章ID


#### 返回的数据结果集

标准结果集

     

#### 返回正确结果示例
```json
{"code":"200","datas":"1"}
```

#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```


### 添加评论

#### 接口功能
本接口添加一条评论数据
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | member_article.comment_add

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 必须 | 登录签名 
id | string | 必须 | 文章ID
message | string | 必须 | 评论内容
image1 | string | 选填 | 图片1
image2 | string | 选填 | 图片2
image3 | string | 选填 | 图片3


#### 返回的数据结果集

整型数据，评论ID

#### 返回正确结果示例
```json
{"code":"200","datas":"5"}
```

#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```


### 回复评论

#### 接口功能
本接口添加一条评论数据
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | member_article.comment_reply

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 必须 | 登录签名 
id | string | 必须 | 评论ID
message | string | 必须 | 评论内容


#### 返回的数据结果集


整型数据，评论ID

     

#### 返回正确结果示例
```json
{"code":"200","datas":"5"}
```

#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```



### 赞评论

#### 接口功能
本接口将评论被赞次数增加1
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | member_article.comment_up

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 必须 | 登录签名 
id | string | 必须 | 评论ID


#### 返回的数据结果集

标准结果集

#### 返回正确结果示例
```json
{"code":"200","datas":"1"}
```

#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```





### 获取发现页面数据

#### 接口功能
本接口获取发现页面数据
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | index.faxian

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 可选 | 登录签名 


#### 返回的数据结果集


| 字段名 |  类型 |  说明
| ---------  | -------- | ------
list | array | 发现项目列表（同食话）

     

#### 返回正确结果示例
同食话

#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```



### 单独上传图片

#### 接口功能
本接口提供单独上传图片功能
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | member_index.upload

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 必填 | 登录签名 
image | file | 必填 | 上传的图片文件（小于4M）
type | string | 选填 | 取值：0文章/评论图片（默认）


#### 返回的数据结果集

| 字段名 |  类型 |  说明
| ---------  | -------- | ------
src | string | 生成的图片地址
     
#### 返回正确结果示例
```json
{"code":"200","datas":{"src":"http://www3.hangowa.com/data/upload/shop/article/05403965810495426.png"}}
```

#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```










## 购物车与结算

### 添加购物车

#### 接口功能
本接口将指定数量的商品加入购物车
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | cart.add

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 必须 | 登录签名 
goods_id | int | 必须 | 商品ID
quantity | int | 必须 | 商品数量

#### 返回的数据结果集

标准结果集

#### 返回正确结果示例
```json
{"code": "200","datas": "1"}
```
#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```

### 购物车列表

#### 接口功能
本接口列出全部购物车相关信息
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | cart.list

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 必须 | 登录签名 

#### 返回的数据结果集


| 字段名 |  类型 |  说明
| ---------  | -------- | ------
cart_list | array | 购物车项目列表
sum | string | 购物车商品总金额
cart_count | string | 购物车商品数量

+ cart_list数组字段

| 字段名 |  类型 |  说明
| ---------  | -------- | ------
store_id | string | 店铺ID
store_name | string | 店铺名称
goods | array | 商品列表

+ goods 数组字段

| 字段名 |  类型 |  说明
| ---------  | -------- | ------
goods_id | string | 商品Sku ID
goods_commonid | string | 通用商品ID
goods_name | string | 商品名称
goods_jingle | string | 商品简介
store_id | string | 店铺ID
store_name | string | 店铺名称
gc_id | string | 商品分类
gc_id_1 | string | 商品以及分类
gc_id_2 | string | 商品二级分类
gc_id_3 | string | 商品三级分类
brand_id | string | 商品品牌ID
goods_price | string | 销售价格
goods_promotion_price | string | 促销价格
goods_promotion_type | string | 促销类型
goods_marketprice | string | 市场价格
goods_cost | string | 成本价格
goods_serial | string | 商品货号
goods_storage_alarm | string | 库存预警值
goods_barcode | string | 条形码
goods_click | string | 商品点击数量
goods_salenum | string | 销售数量
goods_collect | string | 收藏数量
spec_name | string | 规格名称
goods_spec | string | 规格
goods_storage | string | 库存
goods_image | string | 图像路径
goods_body | string | 详情描述
mobile_body | string | 手机版详情
goods_state | string | 商品状态 0下架，1正常，10违规（禁售）
goods_verify | string | 商品审核 1通过，0未通过，10审核中
goods_addtime | string | 添加时间
goods_edittime | string | 编辑时间
areaid_1 | string | 一级地区id
areaid_2 | string | 二级地区id
color_id | string | 颜色规格id
transport_id | string | 运费模板id
goods_freight | string | 运费 0为免运费
goods_vat | string | 是否开具增值税发票 1是，0否
goods_commend | string | 商品推荐 1是，0否
goods_stcids | string | 店铺分类id 首尾用,隔开
evaluation_good_star | string | 好评星级
evaluation_count | string | 评价数
is_virtual | string | 是否为虚拟商品 1是，0否
virtual_indate | string | 虚拟商品有效期
virtual_limit | string | 虚拟商品购买上限
virtual_invalid_refund | string | 是否允许过期退款， 1是，0否
is_fcode | string | 是否为F码商品 1是，0否
is_presell | string | 是否是预售商品 1是，0否
presell_deliverdate | string | 是否是预售商品 1是，0否
is_book | string | 是否为预定商品，1是，0否
book_down_payment | string | 定金金额
book_final_payment | string | 尾款金额
book_down_time | string | 预定结束时间
book_buyers | string | 预定人数
have_gift | string | 是否拥有赠品
is_own_shop | string | 是否为平台自营
contract_1 | string | 消费者保障服务状态 0关闭 1开启
contract_2 | string | 消费者保障服务状态 0关闭 1开启
contract_3 | string | 消费者保障服务状态 0关闭 1开启
contract_4 | string | 消费者保障服务状态 0关闭 1开启
contract_5 | string | 消费者保障服务状态 0关闭 1开启
contract_6 | string | 消费者保障服务状态 0关闭 1开启
contract_7 | string | 消费者保障服务状态 0关闭 1开启
contract_8 | string | 消费者保障服务状态 0关闭 1开启
contract_9 | string | 消费者保障服务状态 0关闭 1开启
contract_10 | string | 消费者保障服务状态 0关闭 1开启
is_chain | string | 是否为门店商品 1是，0否
invite_rate | string | 分销佣金
is_del | string | 是否删除 0未删除，1已删除
certification | string | 资质证明文件
sole_info: | array | 套餐信息
groupbuy_info | string | 团购信息
xianshi_info | string | 限时信息
jjg_info | string | 加价购信息
cart_id | string | 购物车商品ID
goods_num | string | 购物车商品数量
goods_image_url | string | 商品图片URL
gift_list | string | 赠品列表


#### 返回正确结果示例
```json
{
  "code": "200",
  "datas": {
    "cart_list": [
      {
        "store_id": "10",
        "store_name": "恩施土家购特色馆",
        "goods": [
          {
            "goods_id": "100081",
            "goods_commonid": "100070",
            "goods_name": "恩施鹤峰特产 高山百花蜜【集秀】绿盒装500g",
            "goods_jingle": "【全国包邮】 天然、绿色、健康、安全",
            "store_id": "11",
            "store_name": "恩施土家购特色馆",
            "gc_id": "77",
            "gc_id_1": "1",
            "gc_id_2": "7",
            "gc_id_3": "77",
            "brand_id": "443",
            "goods_price": "229.00",
            "goods_promotion_price": "229.00",
            "goods_promotion_type": "0",
            "goods_marketprice": "280.00",
            "goods_cost": "0.00",
            "goods_cost_new": "0",
            "goods_cost_status": "0",
            "goods_serial": "P55F7FDC828F88",
            "goods_storage_alarm": "0",
            "goods_barcode": "",
            "goods_click": "649",
            "goods_salenum": "0",
            "goods_collect": "0",
            "spec_name": "N;",
            "goods_spec": "",
            "goods_storage": "999",
            "goods_image": "2016/11_05242317148883208.jpg",
            "goods_body": "",
            "mobile_body": "",
            "goods_state": "1",
            "goods_verify": "1",
            "goods_addtime": "1470881442",
            "goods_edittime": "1476265572",
            "areaid_1": "17",
            "areaid_2": "270",
            "color_id": "0",
            "transport_id": "8",
            "goods_freight": "0.00",
            "goods_vat": "0",
            "goods_commend": "1",
            "goods_stcids": ",9,",
            "evaluation_good_star": "5",
            "evaluation_count": "0",
            "is_virtual": "0",
            "virtual_indate": "0",
            "virtual_limit": "0",
            "virtual_invalid_refund": "0",
            "is_fcode": "0",
            "is_presell": "0",
            "presell_deliverdate": "0",
            "is_book": "0",
            "book_down_payment": "0.00",
            "book_final_payment": "0.00",
            "book_down_time": "0",
            "book_buyers": "0",
            "have_gift": "0",
            "is_own_shop": "0",
            "contract_1": "0",
            "contract_2": "0",
            "contract_3": "0",
            "contract_4": "0",
            "contract_5": "0",
            "contract_6": "0",
            "contract_7": "0",
            "contract_8": "0",
            "contract_9": "0",
            "contract_10": "0",
            "is_chain": "0",
            "invite_rate": "0.00",
            "is_del": "0",
            "tax_input": "17.000",
            "tax_output": "17.000",
            "certification": "",
            "send_sap": "2",
            "edit_sap": "0",
            "sole_info": [],
            "groupbuy_info": "",
            "xianshi_info": "",
            "jjg_info": "",
            "cart_id": "12269",
            "goods_num": "1",
            "goods_image_url": "http://www2.hangowa.dev.com/data/upload/shop/common/default_goods_image_240.gif",
            "gift_list": ""
          }
        ],
      }
    ],
    "sum": "229.00",
    "cart_count": "1"
  }
}
```
#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```



### 删除购物车商品

#### 接口功能
本接口将指定商品从购物车删除，或者清空用户购物车
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | cart.remove

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 必须 | 登录签名 
cart_id | string | 选填 | 购物车商品ID,多个使用逗号分隔或者使用数组（参见购物车列表商品数组字段），若不填此字段则清空购物车

#### 返回的数据结果集

标准结果集

#### 返回正确结果示例
```json
{"code": "200","datas": "1"}
```
#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```

### 编辑购物车商品数量

#### 接口功能
本接口将指定购物车商品数量修改为指定值
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | cart.edit

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 必须 | 登录签名 
cart_id | int | 必须 | 购物车商品ID（参见购物车列表商品数组字段）
quantity | int | 必须 | 购物车商品数量

#### 返回的数据结果集

标准结果集

#### 返回正确结果示例
```json
{"code": "200","datas": "1"}
```
#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```



### 获取购物车商品总数

#### 接口功能
本接口获取当前用户购物车商品总数
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | cart.edit

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 必须 | 登录签名 

#### 返回的数据结果集

| 字段名 |  类型 |  说明
| ---------  | -------- | ------
count | string | 当前用户购物车商品总数

#### 返回正确结果示例
```json
{"code":"200","datas":{"count":"1"}}
```
#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```


### 提交结算申请


#### 接口功能
本接口提交结算申请
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | buy.step1

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 必须 | 登录签名 
cart_id | string | 必须 |  购物车商品数据 格式：购物车ID &#124; 商品数量,购物车ID &#124; 商品数量,（购物车ID参见购物车列表商品数组字段），示例：'12269 &#124; 2,12270 &#124; 1'
ifcart | integer | 必须 | 是否从购物车提交：1从购物车提交，0直接购买
address_id | integer | 选填 | 用户收货地址ID

#### 返回的数据结果集

| 字段名 |  类型 |  说明
| ---------  | -------- | ------
store_cart_list | array | 当前用户结算商品列表
freight_hash | string | freight hash
address_info | array | 用户收货地址信息
ifshow_offpay | string | 是否显示线下付款
vat_hash | string | vat hash
inv_info | array | 发票信息
available_predeposit | string | 用户预存款余额
available_rc_balance | string | 用户充值卡余额
rpt_list | array | 可用红包列表
zk_list | array | 可用折扣列表
order_amount | string | 订单金额
rpt_info | string | 红包信息
address_api | array | 运费信息
store_final_total_list | array | 店铺金额合计列表


#### 返回正确结果示例
```json
{
  "code": "200",
  "datas": {
    "store_cart_list": {
      "11": {
        "goods_list": [
          {
            "cart_id": "12269",
            "buyer_id": "202204",
            "store_id": "11",
            "store_name": "恩施土家购特色馆",
            "goods_id": "100081",
            "goods_name": "恩施鹤峰特产 高山百花蜜【集秀】绿盒装500g",
            "goods_price": "229.00",
            "goods_num": "2",
            "goods_image": "2016/11_05242317148883208.jpg",
            "bl_id": "0",
            "state": "1",
            "storage_state": "1",
            "goods_commonid": "100070",
            "gc_id": "77",
            "transport_id": "8",
            "goods_freight": "0.00",
            "goods_vat": "0",
            "goods_cost": "0.00",
            "tax_input": "17.000",
            "tax_output": "17.000",
            "goods_storage": "999",
            "goods_storage_alarm": "0",
            "is_fcode": "0",
            "have_gift": "0",
            "groupbuy_info": "",
            "xianshi_info": "",
            "is_book": "0",
            "book_down_payment": "0.00",
            "book_final_payment": "0.00",
            "book_down_time": "0",
            "is_chain": "0",
            "sole_info": [],
            "contractlist": [],
            "goods_total": "458.00",
            "goods_image_url": "http://www2.hangowa.dev.com/data/upload/shop/common/default_goods_image_240.gif"
          }
        ],
        "store_goods_total": "458.00",
        "store_mansong_rule_list": "",
        "store_voucher_list": [],
        "store_voucher_info": [],
        "freight": "1",
        "store_name": "恩施土家购特色馆",
        "store_id": "16"
      },
      "79": {
        "goods_list": [
          {
            "cart_id": "12270",
            "buyer_id": "202204",
            "store_id": "79",
            "store_name": "骏海水产专营店",
            "goods_id": "100876",
            "goods_name": "骏海水产湖北正宗梁子湖大闸蟹公2.8-3.2两 母2.0-2.4两 3对共6只",
            "goods_price": "99.00",
            "goods_num": "1",
            "goods_image": "2016/79_05282980327601489.jpg",
            "bl_id": "0",
            "state": "1",
            "storage_state": "1",
            "goods_commonid": "100859",
            "gc_id": "1156",
            "transport_id": "89",
            "goods_freight": "0.00",
            "goods_vat": "0",
            "goods_cost": "0.00",
            "tax_input": "0.000",
            "tax_output": "0.000",
            "goods_storage": "582",
            "goods_storage_alarm": "58",
            "is_fcode": "0",
            "have_gift": "0",
            "groupbuy_info": "",
            "xianshi_info": "",
            "is_book": "0",
            "book_down_payment": "0.00",
            "book_final_payment": "0.00",
            "book_down_time": "0",
            "is_chain": "0",
            "sole_info": [],
            "contractlist": [],
            "goods_total": "99.00",
            "goods_image_url": "http://www2.hangowa.dev.com/data/upload/shop/common/default_goods_image_240.gif"
          }
        ],
        "store_goods_total": "99.00",
        "store_mansong_rule_list": "",
        "store_voucher_list": [],
        "store_voucher_info": [],
        "freight": "1",
        "store_name": "骏海水产专营店"
      }
    },
    "freight_hash": "MimJhGaIahYv0OYToVZyD4TZ_-ao9TaF7gh2CPKmjT_PTWP",
    "address_info": {
      "address_id": "88",
      "member_id": "202204",
      "true_name": "测试",
      "area_id": "2812",
      "city_id": "258",
      "area_info": "湖北 武汉市 东西湖区",
      "address": "火凤凰",
      "tel_phone": "",
      "mob_phone": "13800138000",
      "is_default": "0",
      "dlyp_id": "0"
    },
    "ifshow_offpay": "",
    "vat_hash": "tkcgUclpVa0-NCqkO9iF0U6moVcHy7NhkSb",
    "inv_info": {
      "content": "不需要发票"
    },
    "available_predeposit": "",
    "available_rc_balance": "0.100",
    "rpt_list": [],
    "zk_list": [],
    "order_amount": "557",
    "rpt_info": "",
    "address_api": {
      "state": "success",
      "content": {
        "11": "0.00",
        "79": "0.00"
      },
      "no_send_tpl_ids": [],
      "allow_offpay": "0",
      "allow_offpay_batch": [],
      "offpay_hash": "YdHEW7puojEcs8JO2leue9lfPFnd2jNgpjR_lEI",
      "offpay_hash_batch": "L1-8X1dMg3V6irwbRCdHwan8hlT3qwu"
    },
    "store_final_total_list": {
      "1": "557.00"
    }
  }
}
```
#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```


### 提交订单

#### 接口功能
本接口提交订单
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | buy.step2

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 必须 | 登录签名 
cart_id | string | 必须 |  购物车商品数据 格式：购物车ID &#124; 商品数量,购物车ID &#124; 商品数量,（直接购买时购物车ID请填商品ID），示例：12269 &#124; 2,12270 &#124; 1
ifcart | integer | 必须 | 是否从购物车提交：1从购物车提交，0直接购买
address_id | integer | 选填 | 用户收货地址ID
vat_hash | string | 必填 | 上一步返回的vat hash
offpay_hash | string | 必填 | 上一步返回的offpay_hash
offpay_hash_batch | string | 必填 | 上一步返回的offpay_hash_batch 
pay_name | string | 选填 | 支付方式名称，online 在线支付，offline线下支付，
invoice_id | integer | 选填 | 发票ID
voucher | string | 选填 | 代金券使用，格式：代金券ID &#124; 商家ID &#124; 代金券金额,代金券ID &#124; 商家ID &#124; 代金券金额 示例：123&#124;11&#124;10.00,123&#124;11&#124;10.00
pd_pay | integer | 选填 | 预存款支付金额
fcode | string | 选填 | F码
rcb_pay | integer | 选填 | 充值卡支付金额
rpt | integer | 选填 | 红包金额
pay_message | string | 选填 | 用户留言<br />格式：商家ID &#124; 留言内容,商家ID &#124; 留言内容<br />示例：10&#124;尽快发货,20&#124;

#### 返回的数据结果集

| 字段名 |  类型 |  说明
| ---------  | -------- | ------
pay_sn | string | 支付单号


#### 返回正确结果示例
```json
{"code":200,"datas":{"pay_sn":"161203205428335007"}}
```
#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```


### 获取支付单信息

#### 接口功能
本接口获取支付单信息
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | buy.pay

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 必须 | 登录签名 
pay_sn | integer | 必须 |  支付单号

#### 返回的数据结果集

| 字段名 |  类型 |  说明
| ---------  | -------- | ------
pay_info | array | 支付单信息


+ pay_info 数组字段

| 字段名 |  类型 |  说明
| ---------  | -------- | ------
pay_amount | string | 支付金额
member_available_pd | string | 用户可用余额
member_available_rcb | string | 用户充值卡余额
member_paypwd | string | 用户是否设置支付密码
pay_sn | string | 支付单号
payed_amount | string | 已支付金额
payment_list | array | 支付方式列表

+ payment_list 数组项目字段

| 字段名 |  类型 |  说明
| ---------  | -------- | ------
channel | string | 支付 渠道代码alipay，wxpay，wxpay_jsapi，alipay_mb
name | string | 支付方式名称：支付宝，微信


#### 返回正确结果示例
```json
{
  "code": "200",
  "datas": {
    "pay_info": {
      "pay_amount": "99.00",
      "member_available_pd": "0.00",
      "member_available_rcb": "0.000",
      "member_paypwd": "1",
      "pay_sn": "161203230398435832",
      "payed_amount": "0.00",
      "payment_list": [
        {
        "channel": "alipay",
        "name": "支付宝"
        },
          {
        "channel": "wx",
        "name": "微信"
        }
      ]
    }
  }
}
```
#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```


### 充值卡支付

#### 接口功能
本接口获取支付单信息
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | buy.rcb_pay

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 必须 | 登录签名 
pay_sn | integer | 必须 |  支付单号
password | string | 必须 |  支付密码

#### 返回的数据结果集

整型数据

#### 返回正确结果示例
```json
{
  "code": "200",
  "datas": 1
}
```
#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```

### 获取Ping++支付对象信息

#### 接口功能
本接口获取Ping++支付对象信息
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | buy.charge

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 必须 | 登录签名 
pay_sn | integer | 必须 |  支付单号
amount | string | 必须 |  支付金额
channel | string | 必须 |  支付渠道

#### 返回的数据结果集

| 字段名 |  类型 |  说明
| ---------  | -------- | ------
charge | array | Ping++支付对象(详见Ping++文档)



#### 返回正确结果示例
```json
{
    "code": 200,
    "datas": {
        "charge": {
            "id": "ch_mHqnHKW50Ge5zXTGGS8aL8W1",
            "object": "charge",
            "created": 1481618497,
            "livemode": false,
            "paid": false,
            "refunded": false,
            "app": "app_LqD8CC88yjT4OmjL",
            "channel": "alipay",
            "order_no": "161203230398435832",
            "client_ip": "192.168.100.1",
            "amount": 12,
            "amount_settle": 12,
            "currency": "cny",
            "subject": "161203230398435832",
            "body": "161203230398435832",
            "extra": [],
            "time_paid": null,
            "time_expire": 1481704897,
            "time_settle": null,
            "transaction_no": null,
            "refunds": {
                "object": "list",
                "url": "/v1/charges/ch_mHqnHKW50Ge5zXTGGS8aL8W1/refunds",
                "has_more": false,
                "data": []
            },
            "amount_refunded": 0,
            "failure_code": null,
            "failure_msg": null,
            "metadata": [],
            "credential": {
                "object": "credential",
                "alipay": {
                    "orderInfo": "_input_charset=\"utf-8\"&body=\"161203230398435832\"&it_b_pay=\"2016-12-14 16:41:37\"&notify_url=\"https%3A%2F%2Fnotify.pingxx.com%2Fnotify%2Fcharges%2Fch_mHqnHKW50Ge5zXTGGS8aL8W1\"&out_trade_no=\"161203230398435832\"&partner=\"2008911238803511\"&payment_type=\"1\"&seller_id=\"2008911238803511\"&service=\"mobile.securitypay.pay\"&subject=\"161203230398435832\"&total_fee=\"0.12\"&sign=\"T0MwbUg0TGVuajVHOThhWHpUYlg5cXZM\"&sign_type=\"RSA\""
                }
            },
            "description": null
        }
    }
}
```
#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```


### 获取支付状态


#### 接口功能
本接口提供验证支付状态功能
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | buy.pay_state

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 必须 | 登录签名 
pay_sn | string | 必须 | 支付单号

#### 返回的数据结果集

| 字段名 |  类型 |  说明
| ---------  | -------- | ------
pay_id | string | 支付单ID
pay_sn | string | 支付单号
api_pay_state | string | 第三方支付状态1已支付，1未支付

#### 返回正确结果示例
```json
{"code":"200","datas":{"pay_id":"3253","pay_sn":"160830192404033160","api_pay_state":"1"}}
```
#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"密码错误"}}
```



### 验证支付密码

#### 接口功能
本接口提供验证支付密码功能
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | buy.verify_password

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 必须 | 登录签名 
password | string | 必须 | 待验证支付密码

#### 返回的数据结果集

标准结果集

#### 返回正确结果示例
```json
{"code": "200","datas": "1"}
```
#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"密码错误"}}
```


### 获取发票列表

#### 接口功能
本接口提供获取发票列表功能
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | invoice.list

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 必须 | 登录签名 

#### 返回的数据结果集

| 字段名 |  类型 |  说明
| ---------  | -------- | ------
invoice_list | array | 用户发票列表

+ invoice_list 数组项目字段

| 字段名 |  类型 |  说明
| ---------  | -------- | ------
inv_id | string | 发票ID
inv_title | string | 发票抬头
inv_content | string | 发票详情

#### 返回正确结果示例
```json
{
  "code": "200",
  "datas": {
    "invoice_list": [
      {
        "inv_id": "1495",
        "inv_title": "个人",
        "inv_content": "明细"
      }
    ]
  }
}
```
#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```

### 获取发票内容列表

#### 接口功能
本接口提供获取发票内容列表功能
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | invoice.content_list

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 必须 | 登录签名 

#### 返回的数据结果集

| 字段名 |  类型 |  说明
| ---------  | -------- | ------
content_list | array | 发票内容列表，详见示例

#### 返回正确结果示例
```json
{
  "code": "200",
  "datas": {
    "content_list": [
      "明细",
      "酒",
      "食品",
      "饮料",
      "玩具",
      "日用品",
      "装修材料",
      "化妆品",
      "办公用品",
      "学生用品",
      "家居用品",
      "饰品",
      "服装",
      "箱包",
      "精品",
      "家电",
      "劳防用品",
      "耗材",
      "电脑配件"
    ]
  }
}
```
#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"参数错误"}}
```



### 删除发票

#### 接口功能
本接口提供删除发票功能
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | invoice.del

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 必须 | 登录签名 
inv_id | integer | 发票ID

#### 返回的数据结果集

标准结果集

#### 返回正确结果示例
```json
{"code": "200", "datas": 1}
```
#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"密码错误"}}
```

### 增加发票

#### 接口功能
本接口提供增加发票功能
#### 方法名称
| 字段名 |  类型 | 取值
| ---------  | -------- | ------
method | string | invoice.add

#### 应用级参数

| 字段名 |  类型 | 约束 | 说明
| ---------  | -------- | ------ | ------
key | string | 必须 | 登录签名 
inv_title| string | 发票抬头 person 个人，公司名称
inv_content | string | 发票内容

#### 返回的数据结果集

标准结果集

#### 返回正确结果示例
```json
{"code": "200", "datas": 1}
```
#### 返回错误结果示例
```json
{"code":"400","datas":{"error":"密码错误"}}
```


