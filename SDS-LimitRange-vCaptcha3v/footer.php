    
    

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3"
        crossorigin="anonymous"></script>
    <script>
        grecaptcha.ready( function() {
            grecaptcha.execute('6LdjhdMiAAAAAFx-AhjULOe5hFvi3uPm4Z4MfOso',
             {action: 'formulario'}).then( function( token ) {
                const itoken = document.getElementById('token');
                itoken.value = token;
                console.log({token});
             })
        });
    </script>

    
</body>
</html>