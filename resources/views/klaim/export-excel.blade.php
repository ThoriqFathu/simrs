<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    <table>
        <thead>
            <tr>
                @foreach ($allKeys as $key)
                    <th>{{ strtoupper(str_replace(['_', '.'], ' ', $key)) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($flattened as $row)
                <tr>
                    @foreach ($allKeys as $key)
                        <td>
                            @php
                                $value = $row[$key] ?? ($key != 'no_rawat' ? 0 : '');

                            @endphp
                            {{ $value }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
