name = input("Введите имя: ")
password = input("Введите пароль: ")
print("Доступ разрешен") if name == "admin" and password == "1234" else print("Доступ запрещен")