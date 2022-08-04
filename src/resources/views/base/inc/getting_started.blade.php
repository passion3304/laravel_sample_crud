<div class="card">
  <div class="card-body">

    <h3>Getting Started</h3>
    <p>If it's your first time using Backpack, we heavily recommend you follow the steps below:</p>

    <div id="accordion" role="tablist">
      <div class="card mb-1">
        <div class="card-header bg-light" id="headingOne" role="tab">
          <h5 class="mb-0"><a data-toggle="collapse" href="#collapseOne" aria-expanded="false" aria-controls="collapseOne" class="collapsed text-dark"><span class="badge badge-warning mr-2 mt-n2">1</span>Create your first CRUD <small class="float-right mt-2">1 min</small></a></h5>
        </div>
        <div class="collapse" id="collapseOne" role="tabpanel" aria-labelledby="headingOne" data-parent="#accordion" style="">
          <div class="card-body">
            <p>You've already got a model, <code class="text-primary bg-light p-1 rounded">App\Models\User</code>... all Laravel projects do. So <strong>let's create a page to administer users</strong>. We want the admin to Create, Read, Update and Delete them. In Backpack, we call that a <a href="https://backpackforlaravel.com/docs/5.x/crud-basics" target="blank">CRUD</a>. And you can easily generate one for an existing Eloquent model, by running:</p>
            <p>
              <code class="text-primary bg-light p-1 rounded">php artisan backpack:crud user</code>
            </p>
            <p>Go ahead, run it. You'll notice it has:</p>
            <ul>
              <li>added an item to the sidebar, in <code class="text-primary bg-light p-1 rounded">resources/views/vendor/backpack/base/inc/sidebar_content.blade.php</code></li>
              <li>added a route, inside <code class="text-primary bg-light p-1 rounded">routes/backpack/custom.php</code></li>
              <li>created <code class="text-primary bg-light p-1 rounded">app/Http/Controllers/Admin/UserCrudController.php</code></li>
              <li>created <code class="text-primary bg-light p-1 rounded">app/Http/Requests/UserRequest.php</code></li>
            </ul>
            <p>You can now click on the new sidebar item (or <a href="{{ backpack_url('user') }}">here</a>) and you'll be able to see the entries in the <code class="text-primary bg-light p-1 rounded">users</code> table. Even though generated CRUDs work out-of-the-box, they might not be <i>exactly</i> what you need. But that's where Backpack shines, in how easy it is to customize.</p>

            <p>To dig a little deeper, <a href="#" data-toggle="collapse" data-target="#customizeUsersCRUD" aria-expanded="true" aria-controls="customizeUsersCRUD">let's make a few changes to the Users CRUD</a>.</p>

            <div class="collapse" id="customizeUsersCRUD">
              <p><strong>1. Let's remove the "password" column</strong> - no point in showing it. To do that, go to <code class="text-primary bg-light p-1 rounded">UserCrudController::setupListOperation()</code> and remove the line saying <code class="text-primary bg-light p-1 rounded">CRUD::column('password');</code>. Easy-peasy, right?</p>
              <p><strong>2. On Create & Update, let's add validation to forms</strong>. There are <a href="https://backpackforlaravel.com/docs/5.x/crud-operation-create#validation" target="_blank">multiple ways to add validation</a>, but for this simple example, let's just use <a href="https://backpackforlaravel.com/docs/5.x/crud-operation-create#validating-fields-using-field-attributes" target="_blank">field attribute validation</a>:</p>
              <ul>
                <li>inside <code class="text-primary bg-light p-1 rounded">UserCrudController</code>, let's remove <code class="text-primary bg-light p-1 rounded">use App\Http\Requests\UserRequest;</code> from the top;</li>
                <li>inside <code class="text-primary bg-light p-1 rounded">UserCrudController</code>, let's remove <code class="text-primary bg-light p-1 rounded">CRUD::setValidation(UserRequest::class);</code> from <code class="text-primary bg-light p-1 rounded">setupCreateOperation()</code>;</li>
                <li>let's delete the <code class="text-primary bg-light p-1 rounded">App\Http\Requests\UserRequest;</code> file;</li>
                <li>now we're left with zero validation inside <code class="text-primary bg-light p-1 rounded">UserCrudController</code>;</li>
                <li>a quick way to add validation is to go to <code class="text-primary bg-light p-1 rounded">setupCreateOperation()</code> and specify validation rules directly on the fields:
                <p>
                  <pre class="text-primary bg-light p-1 rounded">
    CRUD::field('name')->validationRules('required|min:5');
    CRUD::field('email')->validationRules('required|email|unique:users,email');
    CRUD::field('password')->validationRules('required');
                  </pre>
                </p>
                </li>
              </ul>
              <p><strong>3. On Create, let's hash the password.</strong> Ok so... now that we have basic validation, if we create a new User, it'll work. But if you look in the database... you'll notice the password is stored in plain text. We don't want that - we want it hashed. There are multiple ways to achieve this - inside the Model, the Request or the CrudController. Let's use Model Events inside UserCrudController. Here's how our <code class="text-primary bg-light p-1 rounded">setupCreateOperation()</code> would look, with the validation above rules and us tapping into the <code class="text-primary bg-light p-1 rounded">creating</code> event, to hash the password:</p>
              <p>
                <pre class="text-primary bg-light p-1 rounded">
      protected function setupCreateOperation()
      {
          CRUD::field('name')->validationRules('required|min:5');
          CRUD::field('email')->validationRules('required|email|unique:users,email');
          CRUD::field('password')->validationRules('required');

          \App\Models\User::creating(function ($entry) {
              $entry->password = \Hash::make($entry->password);
          });
      }
                </pre>
              </p>
              <p><strong>4. On Update, let's not require the password</strong>. It should only be needed if an admin wants to change it, right? That means the validation rules will be different for "password". But it'll also be different for "email" (on Update we need to pass the ID to the unique rule in Laravel). Since 2/3 rules are different, let's just delete what was inside <code class="text-primary bg-light p-1 rounded">setupUpdateOperation()</code> and code it from scratch:</p>
              <p>
                <pre class="text-primary bg-light p-1 rounded">
      protected function setupUpdateOperation()
      {
          CRUD::field('name')->validationRules('required|min:5');
          CRUD::field('email')->validationRules('required|email|unique:users,email,'.CRUD::getCurrentEntryId());
          CRUD::field('password')->hint('Type a password to change it.');

          \App\Models\User::updating(function ($entry) {
              if (request('password') == null) {
                  $entry->password = $entry->getOriginal('password');
              } else {
                  $entry->password = \Hash::make(request('password'));
              }
          });
      }
                </pre>
              </p>
              <p>That's it. You have a working Users CRUD. Plus, you've already learned some not-so-easy-to-do things, like using Model events inside CrudController and using field-validation instead of form-request-validation. Of course, this only scratches the surface of what Backpack can do. So we heavily recommend you move on to the next step, and learn the basics.</p>
            </div>
          </div>
        </div>
      </div>
      <div class="card mb-1">
        <div class="card-header bg-light" id="headingTwo" role="tab">
          <h5 class="mb-0"><a class="collapsed text-dark" data-toggle="collapse" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo"><span class="badge badge-warning mr-2 mt-n2">2</span>Learn the basics <small class="float-right mt-2">20-30 min</small></a></h5>
        </div>
        <div class="collapse" id="collapseTwo" role="tabpanel" aria-labelledby="headingTwo" data-parent="#accordion" style="">
          <div class="card-body">
            <p>So you've created your first CRUD? Excellent. Now it's time to understand <i>how it works</i> and <i>what else you can do</i>. Time to learn the basics - how to build and customize admin panels using Backpack. Please follow one of the courses below, depending on how you prefer to learn:</p>
            <ul>
              <li><strong><a target="_blank" href="https://backpackforlaravel.com/docs/5.x/getting-started-videos">Video Course</a></strong> - 31 minutes</li>
              <li><strong><a target="_blank" href="https://backpackforlaravel.com/docs/5.x/getting-started-basics">Text Course</a></strong> - 20 minutes</li>
              <li><strong><a target="_blank" href="https://backpackforlaravel.com/getting-started-emails">Email Course</a></strong> - 1 email per day, for 4 days, 5 minutes each</li>
            </ul>
          </div>
        </div>
      </div>
      <div class="card mb-1">
        <div class="card-header bg-light" id="headingThree" role="tab">
          <h5 class="mb-0"><a class="collapsed text-dark" data-toggle="collapse" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree"><span class="badge badge-warning mr-2 mt-n2">3</span>Hide this notice <small class="float-right mt-2">1 min</small></a></h5>
        </div>
        <div class="collapse" id="collapseThree" role="tabpanel" aria-labelledby="headingThree" data-parent="#accordion" style="">
          <div class="card-body">Go to your <code class="text-primary bg-light p-1 rounded">config/backpack/base.php</code> and change <code class="text-primary bg-light p-1 rounded">show_getting_started</code> to <code class="text-primary bg-light p-1 rounded">false</code>.</div>
        </div>
      </div>
    </div>

    <p class="mt-3 mb-0"><small>* this card is only visible on <i>localhost</i>. Follow the last step to hide it from <i>localhost</i> too.</small></p>
  </div>
</div>
