<?php
session_start();

use app\CRUD;

// shell_exec('php ./app/socket.php');

include 'functions.php';

if (!verify_user()) {
    // header('location: login.php');
}


include 'views/header.php';
?>


<div class="logout">
    <a href="logout.php">
        <i class="icon ion-log-out"></i>
    </a>
</div>

<!-- Sm box -->
<div id="app">
    <div class="sm-box text-center alert alert-success fixed-top mx-auto w-50" :class="{'d-none': !smBoxMsg}" role="alert ">{{smBoxMsg}}</div>


    <div class="container my-5">
        <draggable class="row" v-model="accounts" :move="checkMove" @end="updateOrder">

            <div v-for="(account, index) in accounts" class="col-lg-4 col-md-6 mb-4 box">
                <div class="account h-100 p-3 d-flex justify-content-between align-items-center" :class="{itemDrag: checkMove}">
                    <p class="mb-0">
                        {{account.account}}
                    </p>

                    <div class="actions">

                        <transition name="fade-actions">
                            <div v-show="account.isActive" class="btn-group" role="group" aria-label="Basic example">
                                <button type="button" class="btn" data-toggle="modal" data-target="#editModal" @click="editAccount(index)">
                                    <i class="icon ion-android-create"></i>
                                </button>
                                <button type="button" class="btn" data-toggle="modal" data-target="#deleteModal" @click="setDelete = index">
                                    <i class="icon ion-android-delete trash"></i>
                                </button>
                            </div>
                        </transition>


                        <a class="btn" @click="toggle(index)">
                            <i class="icon flip-fade" :class="[account.isActive ? 'ion-android-more-horizontal' :  'ion-android-more-vertical']"></i>
                        </a>

                    </div>

                </div>
            </div>
            <!-- account -->

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="add-new-btn addAccount d-flex justify-content-center align-items-center" data-toggle="modal" data-target="#createModal">
                    <i class="icon ion-android-add m-2"></i>
                </div>
            </div>
        </draggable>







    </div>
    <!-- container -->



    <div class="modal fade" id="showModal" ref="showModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body p-5">
                    <p class="mb-2">
                        <b>Username:</b> {{UsernameInput}}</p>
                    <p class="mb-0">
                        <b>Password:</b> {{PasswordInput}}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="createModal" ref="createModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body p-5">
                    <form @submit="addAccount" action="" method="post">

                        <input class="text-center name mb-3" placeholder="Account" v-model="AccountInput">
                        <input class="text-center username mb-3" placeholder="Username" v-model="UsernameInput">
                        <input class="text-center password mb-3" placeholder="Password " v-model="PasswordInput">

                        <button type="submit" class="btn save-btn createModal">
                            <i class="icon ion-android-done"></i>
                        </button>

                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="editModal" ref="editModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body p-5">
                    <form @submit="saveEdit" action="" method="post">

                        <input class="text-center name mb-3" placeholder="Account" v-model="AccountInput">
                        <input class="text-center username mb-3" placeholder="Username" v-model="UsernameInput">
                        <input class="text-center password mb-3" placeholder="Password " v-model="PasswordInput">

                        <button type="submit" class="btn save-btn editModal">
                            <i class="icon ion-android-done"></i>
                        </button>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="logo my-5 text-center">
            <h1>Kipi</h1>
            <p class="mb-0">
                <b>Password Keeper</b>
                <br> Mini web app for saving your passwords
            </p>
        </div>

    </footer>


</div>



<?php
include 'views/footer.php';
?>



<script type="text/javascript">

    var conn = new WebSocket("ws://localhost:5555/echo");
    conn.onopen = function(e) {
        console.log("Connection established!");
    };
    conn.onmessage = function(e) {};


    var app = new Vue({
        el: "#app",
        data: {
            apiUrl: "functions.php",
            accounts: <?php echo CRUD::getAll() ?>,
            smBoxMsg: false,
            // Add account input
            AccountInput: "",
            UsernameInput: "",
            PasswordInput: "",
            // Edit placeholder
            setEdit: '',
            // Delete placeholder
            setDelete: '',
        },
        mounted: function() {

            $(this.$refs.editModal).on("hidden.bs.modal", this.resetInputs)
        },
        methods: {

            toggle: function(index) {
                this.accounts[index].isActive = !this.accounts[index].isActive;
            },
            smBox: function(msg) {
                if (msg) {
                    this.smBoxMsg = msg;
                }
                setTimeout(() => {
                    this.smBoxMsg = false;
                }, 4000);
            },
            // Draggble
            checkMove: function(e) {
                if (e.draggedContext.element == undefined) {
                    return false;
                } else {
                    return true;
                }
            },
            updateOrder: function() {
                let vm = this;
                let data = [
                    vm.accounts,
                    vm.user_id
                ];
                conn.send(JSON.stringify(data));
            },
            show: function(i) {
                this.UsernameInput = this.accounts[i]['username'];
                this.PasswordInput = this.accounts[i]['password'];
            },
            resetInputs() {
                this.AccountInput = '';
                this.UsernameInput = '';
                this.PasswordInput = '';
            },
            addAccount: function(e) {
                let vm = this;
                e.preventDefault();
                this.accounts.push({
                    account: this.AccountInput,
                    username: this.UsernameInput,
                    password: this.PasswordInput,
                    isActive: false
                });

                this.newAccountInput = "";
                $(this.$refs.createModal).modal("hide");
                this.smBox("Account added successfully");

                let data = [
                    vm.accounts,
                    vm.user_id
                ];
                conn.send(JSON.stringify(data));
            },

            editAccount: function(i) {
                this.setEdit = i;
                this.AccountInput = this.accounts[i]['account'];
                this.UsernameInput = this.accounts[i]['username'];
                this.PasswordInput = this.accounts[i]['password'];
            },
            saveEdit: function(e) {
                let vm = this;
                e.preventDefault();
                let i = this.setEdit;
                this.accounts[i]['account'] = this.AccountInput;
                this.accounts[i]['username'] = this.UsernameInput;
                this.accounts[i]['password'] = this.PasswordInput;
                $(this.$refs.editModal).modal("hide");

                this.smBox("Account updated successfully");
                let data = [
                    vm.accounts,
                    vm.user_id
                ];
                conn.send(JSON.stringify(data));
            },
            deleteAccount: function() {
                let vm = this;
                let item = this.setDelete;
                this.accounts.splice(item)
                let data = [
                    vm.accounts,
                    vm.user_id
                ];
                conn.send(JSON.stringify(data));
                $(this.$refs.deleteModal).modal("hide");
                this.smBox("Account removed successfully");
            }
        }
    });
</script>