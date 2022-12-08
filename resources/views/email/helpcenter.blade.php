<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="description" content="Free Web tutorials">
  <meta name="keywords" content="HTML, CSS, JavaScript">
  <meta name="author" content="John Doe">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Email Title</title>
</head>

<body>

    <h4>{{$title}}</h4>
	<h6>BY: {{ auth()->user()->firstname }} {{ auth()->user()->lastname }}</h6>
	<p>{{$description}}</p>

</body>
</html>