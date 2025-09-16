year = int(input("Введите год: "))
print("Год високосный") if (year % 4 == 0) and (year % 100 != 0) or (year % 400 ==0) else print("Год не високосный")