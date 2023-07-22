<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Upload Form</title>
</head>
<body>

<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script>
    let token = '115|4IP7rxjJpdlBY9Z2wp4HcwM66GayyPfO84tmSRqS';
    axios.patch('https://l2us-res.untaravc.my.id/api/pub/profile-photo',{
        image: 'https://l2us-res.untaravc.my.id/storage/files/20230331/Ywh8zZsIXokhNXLwH7roQ8bng1cQbi_1680275572.png'
    },{
        headers: {
            Authorization: 'Bearer ' + token
        }
    }).then(({data})=>{
        console.log('oke')
    })

</script>
</body>
</html>
