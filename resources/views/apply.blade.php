<form method="POST">
@csrf

<input name="company_name" placeholder="企業名">
<select name="industry_type">
  <option value="beauty">美容院</option>
  <option value="dental">歯科</option>
</select>

<input name="contact_person" placeholder="担当者名">
<input name="email" placeholder="メール">
<input name="phone" placeholder="電話番号">

<textarea name="message"></textarea>

<button>申込み</button>

@if(session('success'))
  {{ session('success') }}
@endif

</form>