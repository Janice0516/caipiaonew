# caipiaonew 纯净版安装说明

本项目为彩票系统的纯净版本，仅包含 **Vue3 用户端前台** 和 **PHP 管理后台**。已移除旧版的冗余前台模板及其他不相关的应用模块。

## 目录结构
- `frontend/` - Vue3 用户端前台源代码（需编译部署）
- `admin/` - PHP 管理后台模块
- `api/`, `source/`, `model/` - PHP 后端核心业务代码
- `configs/` - 数据库和系统配置文件目录
- `database.sql` - 纯净版种子数据库脚本
- `deploy.sh` - 一键编译与权限配置的自动化部署脚本

---

## 部署环境要求
- **操作系统**: Linux (推荐 CentOS 7+/Ubuntu 20.04+)
- **Web 服务器**: Nginx 或 Apache
- **PHP 版本**: PHP 7.0 - 7.4 (推荐搭配 Redis 扩展)
- **数据库**: MySQL 5.6 / 5.7 或 MariaDB 10+
- **前端打包要求**: Node.js (推荐 v16+), npm

---

## 安装与部署步骤

### 1. 导入数据库
1. 登录您的 MySQL 服务器。
2. 创建一个新的数据库（例如 `caipiaonew`）：
   ```sql
   CREATE DATABASE caipiaonew DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
   ```
3. 将项目根目录下的 `database.sql` 导入到该数据库中：
   ```bash
   mysql -u 用户名 -p caipiaonew < database.sql
   ```

### 2. 配置数据库连接
1. 打开 `configs/database.php`。
2. 修改其中的数据库连接信息：
   ```php
   'hostname' => '127.0.0.1', // 数据库地址
   'database' => 'caipiaonew', // 数据库名
   'username' => '您的数据库账号',
   'password' => '您的数据库密码',
   'tablepre' => 'bc_',
   ```

### 3. 后端权限及前端一键部署
赋予根目录下的 `deploy.sh` 执行权限，并执行它。此命令将自动编译 `frontend` 目录下的 Vue3 项目，并赋予后端必要的缓存路径读写权限：
```bash
chmod +x deploy.sh
./deploy.sh
```
*注：如果您没有 Node.js 环境或无法一键编译，请在本地电脑上进入 `frontend` 目录执行 `npm install` 和 `npm run build`，然后将 `dist` 目录上传至服务器。*

### 4. 站点运行目录配置 (Nginx 配置示例)
将您的网站根目录指向当前项目的**根目录**，并配置 URL 重写规则，以及正确映射 Vue3 下打包的前端 `dist` 文件夹（可选，或者将 `dist` 移出供单独域名使用）。

常见 Nginx 配置代码片段（将前端独立部署为一个子目录或静态资源）：
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /www/wwwroot/caipiaonew; # 配置至项目根目录
    index index.php index.html index.htm;
    
    # PHP 解析支持
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### 5. 常见问题排查
1. **空白页面/500错误**：检查 PHP 的运行错误日志；检查 `caches/` 和 `uppic/` 文件夹是否拥有 `777` 读写权限。
2. **后台无法登录**：确认 `database.sql` 已成功导入，并且 `configs/database.php` 数据库连接正常。后台入口文件为根目录下的 `admin.php`。
