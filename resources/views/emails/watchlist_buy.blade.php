@extends('emails.layout.template')
@section('content')

    <div style="">
        <p>Hello, {{ $name}}</p>
        <p>
            You are receiving this alert because you set the following milestone:
        </p>
        <p>
            @if(isset($rulesMet['market_cap']) && $rulesMet['market_cap'])
                Marketcap <br>
            @endif
            @if(isset($rulesMet['liquidity']) && $rulesMet['liquidity'])
                Liquidity <br>
            @endif
            @if(isset($rulesMet['gap']) && $rulesMet['gap'])
                GAP <br>
            @endif
            @if(isset($rulesMet['cpp']) && $rulesMet['cpp'])
                CPP Ask/Bid <br>
            @endif
            @if(isset($rulesMet['prr']) && $rulesMet['prr'])
                PRR <br>
            @endif
             <br>
        </p>
        <p>Your added watchlist <b>{{ $watchlist->coin }}</b> from <b>{{ $watchlist->exchange }}</b> were matched {{ $matchDate }}.</p>
        <p>Feel free to login to  your CMB to continue on your trades.</p>
        <p></p>
        <table style="border: 1px solid #d0d0d0;color: #677d9d; font-family: Roboto, 'Helvetica Neue', sans; width: 100%;border-collapse: collapse;overflow-x: scroll;"
               border="1">
            <tr style="background-color: #ebf0f5;font-size: 13px;font-weight: 400;line-height: 34px;padding: 0 10px;border: 1px solid #d0d0d0;">
                <td style="border: 1px solid #d0d0d0;background-color: #ebf0f5;text-align: left; height: 20px; padding-left: 5px;">
                    Exchange
                </td>
                <td style="border: 1px solid #d0d0d0;background-color: #ebf0f5;text-align: left; height: 20px; padding-left: 5px;">
                    Coin
                </td>
                <td style="border: 1px solid #d0d0d0;background-color: #ebf0f5;text-align: left; height: 20px; padding-left: 5px;">
                    GAP
                </td>
                <td style="border: 1px solid #d0d0d0;background-color: #ebf0f5;text-align: left; height: 20px; padding-left: 5px;">
                    CPP
                </td>
                <td style="border: 1px solid #d0d0d0;background-color: #ebf0f5;text-align: left; height: 20px; padding-left: 5px;">
                    PRR
                </td>
            </tr>
            <tr style="font-size: 12px;font-weight: 400;line-height: 34px;padding: 0 10px;border: 1px solid #d0d0d0;">
                <td style="border: 1px solid #d0d0d0;text-align: left; height: 20px; padding-left: 5px;">{{ $watchlist->exchange }}</td>
                <td style="border: 1px solid #d0d0d0;text-align: left; height: 20px; padding-left: 5px;">{{ $watchlist->coin }}</td>
                <td style="border: 1px solid #d0d0d0;text-align: left; height: 20px; padding-left: 5px;">{{ $watchlist->gap }}</td>
                <td style="border: 1px solid #d0d0d0;text-align: left; height: 20px; padding-left: 5px;">{{ $watchlist->cpp }}</td>
                <td style="border: 1px solid #d0d0d0;text-align: left; height: 20px; padding-left: 5px;">{{ $watchlist->prr }}</td>
            </tr>
            <tr style="background-color: #ebf0f5;font-size: 13px;font-weight: 400;line-height: 34px;padding: 0 10px;border: 1px solid #d0d0d0;">
                <td style="border: 1px solid #d0d0d0;background-color: #ebf0f5;text-align: left; height: 20px; padding-left: 5px;">
                    Liquidity Bought
                </td>
                <td style="border: 1px solid #d0d0d0;background-color: #ebf0f5;text-align: left; height: 20px; padding-left: 5px;">
                    Liquidity Sold
                </td>
                <td style="border: 1px solid #d0d0d0;background-color: #ebf0f5;text-align: left; height: 20px; padding-left: 5px;">
                    Liquidity Score
                </td>
                <td style="border: 1px solid #d0d0d0;background-color: #ebf0f5;text-align: left; height: 20px; padding-left: 5px;">
                    Market Cap Score
                </td>
                <td style="border: 1px solid #d0d0d0;background-color: #ebf0f5;text-align: left; height: 20px; padding-left: 5px;">
                    Overall Score
                </td>

            </tr>
            <tr style="font-size: 12px;font-weight: 400;line-height: 34px;padding: 0 10px;border: 1px solid #d0d0d0;">
                <td style="border: 1px solid #d0d0d0;text-align: left; height: 20px; padding-left: 5px;">{{ $watchlist->btc_liquidity_bought }}</td>
                <td style="border: 1px solid #d0d0d0;text-align: left; height: 20px; padding-left: 5px;">{{ $watchlist->btc_liquidity_sold }}</td>
                <td style="border: 1px solid #d0d0d0;text-align: left; height: 20px; padding-left: 5px;">{{ $watchlist->btc_liquidity_score }}</td>
                <td style="border: 1px solid #d0d0d0;text-align: left; height: 20px; padding-left: 5px;">{{ $watchlist->market_cap_score }}</td>
                <td style="border: 1px solid #d0d0d0;text-align: left; height: 20px; padding-left: 5px;">{{ $watchlist->overall_score }}</td>

            </tr>
        </table>
    </div>

@endsection