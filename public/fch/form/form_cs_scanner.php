<?php 
    include '../inc/head_scripts.php';
    include '../inc/Pre-loader.php';
?>
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <!-- Main-body start -->
                            <div class="main-body">
                                <div class="page-wrapper">
                                    <!-- Page-header start -->
                                    <div class="page-header">
                                        <div class="row align-items-end">
                                            <div class="col-lg-8">
                                                <div class="page-header-title">
                                                    <div class="d-inline">
                                                        <h4>Forms Wizard</h4>
                                                        <span>Lorem ipsum dolor sit <code>amet</code>, consectetur
                                                            adipisicing elit</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="page-header-breadcrumb">
                                                    <ul class="breadcrumb-title">
                                                        <li class="breadcrumb-item"  style="float: left;">
                                                            <a href="../index-2.html"> <i class="feather icon-home"></i> </a>
                                                        </li>
                                                        <li class="breadcrumb-item"  style="float: left;"><a href="#!">Form Wizard</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Page-header end -->

                                    <!-- Page body start -->
                                    <div class="page-body">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <!-- Form wizard with validation card start -->
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>Form Wizard With Validation</h5>
                                                        <span>Add class of <code>.form-control</code> with
                                                            <code>&lt;input&gt;</code> tag</span>

                                                    </div>
                                                    <div class="card-block">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div id="wizard">
                                                                    <section>
                                                                        <form class="wizard-form"
                                                                            id="example-advanced-form" action="#">
                                                                            <h3> Registration </h3>
                                                                            <fieldset>
                                                                                <div class="form-group row">
                                                                                    <div class="col-md-4 col-lg-2">
                                                                                        <label for="userName-2"
                                                                                            class="block">User name
                                                                                            *</label>
                                                                                    </div>
                                                                                    <div class="col-md-8 col-lg-10">
                                                                                        <input id="userName-2"
                                                                                            name="userName" type="text"
                                                                                            class="required form-control">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group row">
                                                                                    <div class="col-md-4 col-lg-2">
                                                                                        <label for="email-2"
                                                                                            class="block">Email
                                                                                            *</label>
                                                                                    </div>
                                                                                    <div class="col-md-8 col-lg-10">
                                                                                        <input id="email-2" name="email"
                                                                                            type="email"
                                                                                            class="required form-control">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group row">
                                                                                    <div class="col-md-4 col-lg-2">
                                                                                        <label for="password-2"
                                                                                            class="block">Password
                                                                                            *</label>
                                                                                    </div>
                                                                                    <div class="col-md-8 col-lg-10">
                                                                                        <input id="password-2"
                                                                                            name="password"
                                                                                            type="password"
                                                                                            class="form-control required">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group row">
                                                                                    <div class="col-md-4 col-lg-2">
                                                                                        <label for="confirm-2"
                                                                                            class="block">Confirm
                                                                                            Password *</label>
                                                                                    </div>
                                                                                    <div class="col-md-8 col-lg-10">
                                                                                        <input id="confirm-2"
                                                                                            name="confirm"
                                                                                            type="password"
                                                                                            class="form-control required">
                                                                                    </div>
                                                                                </div>
                                                                            </fieldset>
                                                                            <h3> General information </h3>
                                                                            <fieldset>
                                                                                <div class="form-group row">
                                                                                    <div class="col-md-4 col-lg-2">
                                                                                        <label for="name-2"
                                                                                            class="block">First name
                                                                                            *</label>
                                                                                    </div>
                                                                                    <div class="col-md-8 col-lg-10">
                                                                                        <input id="name-2" name="name"
                                                                                            type="text"
                                                                                            class="form-control required">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group row">
                                                                                    <div class="col-md-4 col-lg-2">
                                                                                        <label for="surname-2"
                                                                                            class="block">Last name
                                                                                            *</label>
                                                                                    </div>
                                                                                    <div class="col-md-8 col-lg-10">
                                                                                        <input id="surname-2"
                                                                                            name="surname" type="text"
                                                                                            class="form-control required">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group row">
                                                                                    <div class="col-md-4 col-lg-2">
                                                                                        <label for="phone-2"
                                                                                            class="block">Phone
                                                                                            #</label>
                                                                                    </div>
                                                                                    <div class="col-md-8 col-lg-10">
                                                                                        <input id="phone-2" name="phone"
                                                                                            type="number"
                                                                                            class="form-control required phone">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group row">
                                                                                    <div class="col-md-4 col-lg-2">
                                                                                        <label for="date"
                                                                                            class="block">Date Of
                                                                                            Birth</label>
                                                                                    </div>
                                                                                    <div class="col-md-8 col-lg-10">
                                                                                        <input id="date"
                                                                                            name="Date Of Birth"
                                                                                            type="text"
                                                                                            class="form-control required date-control">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group row">
                                                                                    <div class="col-md-4 col-lg-2">
                                                                                        Select Country</div>
                                                                                    <div class="col-md-8 col-lg-10">
                                                                                        <select
                                                                                            class="form-control required">
                                                                                            <option>Select State
                                                                                            </option>
                                                                                            <option>Gujarat</option>
                                                                                            <option>Kerala</option>
                                                                                            <option>Manipur</option>
                                                                                            <option>Tripura</option>
                                                                                            <option>Sikkim</option>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            </fieldset>
                                                                            <h3> Education </h3>
                                                                            <fieldset>
                                                                                <div class="form-group row">
                                                                                    <div class="col-md-4 col-lg-2">
                                                                                        <label for="University-2"
                                                                                            class="block">University</label>
                                                                                    </div>
                                                                                    <div class="col-md-8 col-lg-10">
                                                                                        <input id="University-2"
                                                                                            name="University"
                                                                                            type="text"
                                                                                            class="form-control required">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group row">
                                                                                    <div class="col-md-4 col-lg-2">
                                                                                        <label for="Country-2"
                                                                                            class="block">Country</label>
                                                                                    </div>
                                                                                    <div class="col-md-8 col-lg-10">
                                                                                        <input id="Country-2"
                                                                                            name="Country" type="text"
                                                                                            class="form-control required">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group row">
                                                                                    <div class="col-md-4 col-lg-2">
                                                                                        <label for="Degreelevel-2"
                                                                                            class="block">Degree level
                                                                                            #</label>
                                                                                    </div>
                                                                                    <div class="col-md-8 col-lg-10">
                                                                                        <input id="Degreelevel-2"
                                                                                            name="Degree level"
                                                                                            type="text"
                                                                                            class="form-control required phone">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group row">
                                                                                    <div class="col-md-4 col-lg-2">
                                                                                        <label for="datejoin"
                                                                                            class="block">Date
                                                                                            Join</label>
                                                                                    </div>
                                                                                    <div class="col-md-8 col-lg-10">
                                                                                        <input id="datejoin"
                                                                                            name="Date Of Birth"
                                                                                            type="text"
                                                                                            class="form-control required">
                                                                                    </div>
                                                                                </div>
                                                                            </fieldset>
                                                                            <h3> Work experience </h3>
                                                                            <fieldset>
                                                                                <div class="form-group row">
                                                                                    <div class="col-md-4 col-lg-2">
                                                                                        <label for="Company-2"
                                                                                            class="block">Company:</label>
                                                                                    </div>
                                                                                    <div class="col-md-8 col-lg-10">
                                                                                        <input id="Company-2"
                                                                                            name="Company:" type="text"
                                                                                            class="form-control required">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group row">
                                                                                    <div class="col-md-4 col-lg-2">
                                                                                        <label for="CountryW-2"
                                                                                            class="block">Country</label>
                                                                                    </div>
                                                                                    <div class="col-md-8 col-lg-10">
                                                                                        <input id="CountryW-2"
                                                                                            name="Country" type="text"
                                                                                            class="form-control required">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group row">
                                                                                    <div class="col-md-4 col-lg-2">
                                                                                        <label for="Position-2"
                                                                                            class="block">Position</label>
                                                                                    </div>
                                                                                    <div class="col-md-8 col-lg-10">
                                                                                        <input id="Position-2"
                                                                                            name="Position" type="text"
                                                                                            class="form-control required">
                                                                                    </div>
                                                                                </div>
                                                                            </fieldset>
                                                                        </form>
                                                                    </section>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Form wizard with validation card end -->
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Page body end -->
                                </div>
                            </div>
                            <!-- Main-body end -->

                            <div id="styleSelector">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php 
    include_once '../inc/footer.php';
?>