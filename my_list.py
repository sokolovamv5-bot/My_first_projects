my_list = []
for i in range(5):
    my_list.append(int(input("Введите число: ")))
print(my_list)
sum = 0
for i in range(len(my_list)):
    sum += my_list[i]
print(sum, sum/len(my_list))