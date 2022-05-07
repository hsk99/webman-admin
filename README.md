
# Pear Admin Webman

## 项目简介
> Pear Admin Webman 基于 [webman](https://www.workerman.net/webman "webman") + [Pear Admin Layui](http://www.pearadmin.com "Pear Admin Layui") 开发，参考 [Pear Admin Think](https://gitee.com/pear-admin/Pear-Admin-Think "Pear Admin Think") 改造实现

> 演示地址：https://dev.hsk99.com.cn/

> 账号 ` demo ` 密码 ` demo `


## 功能介绍

- 应用监控
- 服务异常通知
- 管理员
- 角色权限
- 权限控制
- 一键CRUD
- 菜单管理
- 日志管理
- 文件管理
- 系统设置
- 表单生成
- OSS存储
- 七牛云存储


## 安装配置

#### composer 安装

- 创建项目 ` composer create-project hsk99/webman-admin `

#### 下载安装

- 克隆项目 ` git clone https://github.com/hsk99/webman-admin `

- 安装包依赖 ` composer install `

#### 修改配置

- 数据库 ` config/thinkorm.php `

- Redis ` config/redis.php `

- RedisQueue ` config/plugin/webman/redis-queue/redis.php `

- 服务异常通知 ` config/plugin/hsk99/exception/app.php `

#### 运行访问

- 启动服务 ` php start.php start `

- 浏览器访问 ` http://127.0.0.1:8787 `


## CRUD生成

> ` config/app.php ` debug = true

- 第一步 约定字段类型必须"XXX_XXX"

- 第二步 选择数据表生成。

- 建议定义软删除delete_time，自动生成回收站功能。如不需要可自行删除。


## 后台入口修改

- 修改 ` app ` 目录下 ` admin ` 文件夹名称

- 修改 ` app/admin ` 目录下文件的命名空间 ` app\admin `

- 修改 ` config/middleware.php ` 中间件的配置


## 项目声明

> 仅供技术研究使用，请勿用于非法用途，否则产生的后果作者概不负责。