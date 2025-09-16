my_list = []
for i in range(5):
    my_list.append(int(input("Введите число: ")))
print(my_list)
even = []
sum = 0
for i in range(len(my_list)):
    if my_list[i] % 2 == 0:
        even.append(my_list[i])
print(even)