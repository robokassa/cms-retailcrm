# Официальный модуль платежной системы Робокасса для RetailCRM.

Для публикации на новом сервере нужно:
1. скопировать PHP файлы
2. В файле /.cfg.php прописать настройки доступа к БД (MySQL)
3. Импортировать структуру таблиц из дампа БД в каталоге /sql

Для окончательного переноса рабочей версии:
5. Сменить IP домена retail.robokassa.ru
6. Скопировать пользовательские данные в БД с текущего сервера
