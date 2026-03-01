-- 重置用户端测试账号密码为 admin123
-- Docker: docker exec -i <mysql容器名> mysql -uroot -proot123 music_platform < database/reset_user_password.sql

UPDATE `users` SET `password` = '$2y$10$xrnGd9tCaH6dY.Iylbw2sumQZrG7Fvo1tv39C8yY6kghl6zvxO0F.' WHERE `email` = 'user@test.com';
