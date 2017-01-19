<?php /** 載入dexie主程式 */  ?>
<script src="node_modules/dexie/dist/dexie.min.js"></script>
<script>
/**
 * 一、這個範例要做什麼？
 * 好比說我的PostgreSQL資料庫中有users這張資料表，裡面有id、使用者帳號、密碼，我透過JSON格式將資料存到Client端的indexedDB中，然後進行CRUD
 *
 * 二、它的使用情境有那些？
 * 好比我用樹莓開發爬蟲，然後好死不死網路斷了，我不能回傳，需要暫存
 * 好比我用PHP開發POS機，POS一般來說不會完全都靠Server/Client，不然網路斷了就好玩了
 * 好比說我開發商城、首頁、類別頁、商品頁，這些有用到的HTML資料也可以先暫存到Client端的Borwser中
 * 其餘又好比Web App、Mobile App需離線運用等
 *
 * 三、如何確保Server資料與Client資料一致性
 * 和一般Server端資料庫不一樣的地方，indexedDB的資料是是靠版本號來判斷Client是否與Server的Schema與Data是一致的
 *
 * 三、indexedDB是什麼？
 * indexedDB和Local Storage與Session Storage一樣，是Client端的Key/Value儲存空間，讓你可以將一些資料暫時存放在Client端，減少對Server的存取或是離線使用
 *
 * 四、Dexie是什麼？
 * 他是一套讓你indexedDB可以透過類SQL語法存取的強大JS套件，讓你可以像平日下SQL存取資料庫一般進行CRUD，然後本套件我是用npm抓下來的
 *
 * 五、npm是什麼？
 * 和PHP之於Composer，與Python之於pip一樣，是nodejs的套式管理程式，你去官網抓nodejs回來安裝進去之後，就可以進入DOS/Shell透過以下指令進行套件管理
 *
 * npm search [套件名]  //查詢是否有該js套件可以安裝，如果我要安裝bootstrap就是下npm install bootstrap
 * npm install [套件名] //安裝js套件到專案
 * npm uninstall [套件名] //移除專案中己安裝的js套件
 * npm update [套件名] //更新你專案裡的套件
 *
 * 其它還有不懂的請下 npm help
 * 一般來說，npm會在你所在的資料夾底下新增一個node_modules資料夾，供存放套件用
 */

/**
 * API 參考頁面
 * https://github.com/dfahlander/Dexie.js/wiki
 *
 * 一進去之後，基本上我不會點左邊的連結，只會直接點右邊的API列表的連結
 *
 * 以下程式範例開始
 */

/**
 * 資料庫v1 schema
 * 我有一張users資料表，裡面有id(主鍵，自動遞增), name, account, password等欄位
 *
 * Dexie習慣將陣列的第零筆當做是主鍵，要自動遞增就在前面加上++
 */
const schema1 = {users:'++id,name,account,password'};

/** 資料庫v2 schema **/
const schema2 = {
    users:'++id,groupId,name,account,password',
    group:'++id,name'
};

/** 使用者資料表v1 data **/
const data = [
    {name:'Peter Cheng',account:'T00052',password:'1qazse4rfvgy7'},
    {name:'Tom Cheng',account:'T00053',password:'1qazse4rfvgy7'},
    {name:'John Cheng',account:'T00054',password:'1qazse4rfvgy7'},
    {name:'Joe Cheng',account:'T00055',password:'1qazse4rfvgy7'},
    {name:'Ryan Cheng',account:'T00056',password:'1qazse4rfvgy7'}
];

/** 定義Client資料庫 **/
var db = new Dexie("test");

/** 建立版本2的資料庫 **/
//db.version(2).stores(schema2);

/** 建立版本1的資料庫 **/
db.version(1).stores(schema1);

/** 刪除資料庫 **/
//db.delete();

db.open().catch(function (e) {
    /** 資料庫開啟失敗時 **/
});

/**
 * 新增資料
 * add
 * @param array data 新增用的資料
 */
function add(data) {
    /** 開啟Transaction 進行新增資料 **/
    return db.transaction('rw', db.users, function() {
        db.users.clear();
        for(key in data) {
            db.users.add(data[key]);
        }
    }).then(function(result) {
        /** 成功committed時 **/
    }).catch(function(error) {
        /** 失敗時 **/
    });
}

/**
 * 透過姓名取得資料
 * getUsersByName
 * @return object
 */
function getUsersByName() {
    return db.users.where('name').equals('Peter Cheng').each(function(user) {
        console.log(user);
    });
}

/**
 * 透過多個姓名取得資料
 * getUsersByInName
 * @return object
 */
function getUsersByInName() {
    return db.users.where('name').anyOf('Peter Cheng', 'Tom Cheng', 'Joe Cheng').each(function(user) {
        console.log(user);
    });
}

/**
 * 透過模糊搜尋取得資料
 * getUsersByLikeName
 * @return object
 */
function getUsersByLikeName() {
    return db.users.where('name').startsWithIgnoreCase('Jo').each(function(user) {
        console.log(user);
    });
}

/**
 * 透過姓名或是帳號取得資料
 * getUsersByNameOrAccount
 * @return object
 */
function getUsersByNameOrAccount() {
    return db.users.where('name').startsWithIgnoreCase('Jo').or('account').equals('T00052').each(function(user) {
        console.log(user);
    });
}

/**
 * 透過帳號與姓名取得資料
 * getUsersByAccountAndName
 * @return object
 */
function getUsersByAccountAndName() {
    return db.users.where('account').startsWithIgnoreCase('T000').and(function(user) {return user.name = 'Peter Cheng';}).each(function(user) {
        console.log(user);
    });
}

/**
 * 透過大於...序列號取得資料
 * getUsersByIdAbove
 * @return object
 */
function getUsersByIdAbove() {
    return db.users.where('id').above(3).each(function(user) {
        console.log(user);
    });
}

/**
 * 透過小於...序列號取得資料
 * getUsersByIdBelow
 * @return object
 */
function getUsersByIdBelow() {
    return db.users.where('id').below(3).each(function(user) {
        console.log(user);
    });
}

/**
 * 透過名稱修改資料
 * updateUserByName
 * @return object
 */
function updateUserByName() {
    return db.users.where('name').equals('Peter Cheng').modify({name:'Bill Cheng',account:'T00057'});
}

/**
 * 透過名稱刪除資料
 * deleteUserByName
 * @return object
 */
function deleteUserByName() {
    return db.users.where('name').equals('Bill Cheng').delete().then(function(deleteCount) {
        console.log('Deleted ' + deleteCount + ' objects');
    });
}


//add(data);
//getUsersByName();
//getUsersByInName();
//getUsersByLikeName();
//getUsersByNameOrAccount();
//getUsersByAccountAndName();
//getUsersByIdAbove();
//getUsersByIdBelow();
//updateUserByName();
deleteUserByName();
</script>
