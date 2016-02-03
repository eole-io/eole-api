# API documentation

## Players

Create, view, edit players.

- Get all players

`GET api/players`

``` json
[
    {
        "id":​1,
        "username":"Guest 54924",
        "date_created":"2016-02-03T02:50:44+0100",
        "guest":true,
        "roles":[
            "ROLE_PLAYER"
        ]
    },
    {
        "id":​2,
        "username":"Geraldine",
        "date_created":"2016-02-03T02:51:10+0100",
        "guest":false,
        "roles":[
            "ROLE_PLAYER"
        ]
    }
]
```

- Get a specific player

`GET api/players/geraldine`

``` json
{
    "id":​2,
    "username":"Geraldine",
    "date_created":"2016-02-03T02:51:10+0100",
    "guest":false,
    "roles":[
        "ROLE_PLAYER"
    ]
}
```


## Games

Get all games data.

- Get all installed games

`GET api/games`

``` json
[
    {
        "id":​1,
        "name":"tictactoe"
    },
    {
        "id":​2,
        "name":"awale"
    }
]
```

- Get a game

`GET api/games/`

``` json
{
    "id":​2,
    "name":"awale"
}
```
