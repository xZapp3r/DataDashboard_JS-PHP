$(document).ready(() => {
  $('#documentacao').on('click', (e) => {
    //Requerindo paginas Suporte e Documentacao
    //metodo alternativo $('#pagina').load('documentacao.html')
    $.get('documentacao.html', (data) => {
      // HTML da pagina console.log(data)
      $('#pagina').html(data)
    })
  })
  $('#suporte').on('click', () => {
    //Subindo HTML suporte no ID pagina
    $('#pagina').load('suporte.html')
  })

  $('#atualiza').on('change', (e) => {
    
      //recuperando valor do select
      let competencia = $(e.target).val()
      //Definimos, metodo, url, dados(envio), tipo de dados(retorno), sucesso, erro

      $.ajax({  //objeto literal
        type: 'GET',
        url: 'app.php',
        //tipo de passagem de parametro x-www-form-urlencoded
        data: `atualiza=${competencia}`, //backtix for ecma using variables
        dataType: 'json',
        success: (dados) => {
          //setting data dynamically
          $('#Sell').html(dados.numeroVendas)
          $('#Conversion').html(dados.totalVendas)
          $('#Ativos').html(dados.clientesOur.act)
          $('#Inativos').html(dados.clientesOur.una)
          $('#complain').html(dados.contato[0])
          $('#praise').html(dados.contato[1])
          $('#sugestion').html(dados.contato[2])
          $('#despesa').html(dados.totalDespesas)
          console.log(dados)
        },
        error: (erro) => {console.log(erro.responseText)}        
      })
  })
})

