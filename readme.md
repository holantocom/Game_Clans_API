# API для работы с кланами.

1. POST /api/clan/create/{userID} - создание клана. UserID - id пользователя, выполняющего действие.
	POST params:
		name (string) -  название клана.
		desc (string) - описание клана.
		users (array) - массив id пользователей. 

2. GET /api/clan/lists -  список кланов

3. POST /api/clan/remove/{userID} - удаление клана. UserID - id пользователя, выполняющего действие.

4. POST /api/clan/desc/{userID} - поменять описание клана. UserID - id пользователя, выполняющего действие.
	POST params:
		desc (string) - описание клана.

5. POST /api/user/add/{userID} - добавление пользователя в клан. UserID - id пользователя, выполняющего действие.
	POST params:
		childUserID (integer) - id желаемого пользователя.

6. POST /api/user/remove/{userID} - удаление пользователя из клана. UserID - id пользователя, выполняющего действие.
	POST params:
		childUserID (integer) - id желаемого пользователя.

7. POST /api/user/change/{userID} - редактирование привилегий пользователя в клане. UserID - id пользователя, выполняющего действие.
	POST params:
		childUserID (integer) - id желаемого пользователя.
		role (integer) - роль пользователя.

# Установка

Поместите все приведенные файлы в требуему директорию. Дополнительная установка не требуется. 