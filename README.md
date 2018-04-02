## Shop REST Api v1

Enity list: users, merchants, coupons

You can:
* get users list
* get user by token
* add new user (POST method)
* update user by token (PATCH method)
* delete user by token (DELETE method)
* get merchantslist with coupons
* delete merchantslist by id (DELETE method)
* get coupon list by merchantslist id
* get coupon list by user id

Examples:
* https://46j.ru/api/v1/users/
* https://46j.ru/api/v1/users/X66800988890112983045ac1176ab3b928.07902327/
* POST https://46j.ru/api/v1/users/
* PATCH https://46j.ru/api/v1/users/X66800988890112983045ac1176ab3b928.07902327/
* DELETE https://46j.ru/api/v1/users/X66800988890112983045ac1176ab3b928.07902327/
* https://46j.ru/api/v1/merchants/
* DELETE https://46j.ru/api/v1/merchants/7
* https://46j.ru/api/v1/coupons/mid/4
* https://46j.ru/api/v1/coupons/uid/4


JSON example for user add:
[{"name":"newuser11","email":"utest11@ex.ex","password":"123456","datetime":"2018-04-02 01:01:04"}]

JSON example for update user:
[{"name":"newname"}]
