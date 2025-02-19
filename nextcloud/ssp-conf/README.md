
```
docker exec -it nextcloud-db mariadb -u root -p

-- 新しいデータベースを作成
CREATE DATABASE self_service CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- self-service-password 用ユーザーを作成
CREATE USER 'ssp_user'@'%' IDENTIFIED BY 'ssp_securepassword';

-- self_service データベースへの全権限を付与
GRANT ALL PRIVILEGES ON self_service.* TO 'ssp_user'@'%';

-- 権限を有効化
FLUSH PRIVILEGES;

USE self_service;

CREATE TABLE password_reset_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    request_details TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO password_reset_requests (username, request_details, status, created_at) 
VALUES ('testuser', 'Password reset request', 'pending', NOW());
