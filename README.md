#Magento - Cálculo de Frete dos Correios
O módulo tem como função principal calcular os preços e prazos utilizando o webservice disponibilizado pelos Correios.

##Funcionalidades
* Inclui todos os métodos de entrega disponíveis.
* Configuração para comerciantes que tem contrato com os Correios.
* Rastreador de objeto em tempo real.
* Escolha do método de frete grátis inteligente ( segue regras de promoção ).
* Possibilidade de adicionar dias ao prazo de entrega.
* Possibilidade de adicionar taxa de manuseio fixa ou percentual.

##Instalação
Para efetuar a instalação do módulo siga os passos:

1. Acesse o link do módulo no *Magento Connect* e clique em *Install Now*.
2. Clique em *logged* caso já tenha uma conta no *Magento Connect*. Caso contrário clique em *registered* e registre-se.
3. Aceite os termos e condições de uso clicando no checkbox e clique em *Get Extension Key*. 
4. Copie o endereço de instalação no módulo fornecido pelo *Magento Connect*.
5. Entre na administração de seu *Magento* e vá no menu *System > Magento Connect > Magento Connect Manager*. 
6. Coloque novamente seu login e senha e clique em *Log In*. 
7. Cole o endereço de instalação do módulo no campo *Paste extension key to install* e clique em *Install*.
8. Em *Extension dependencies* clique em *Proceed*.

##Configuração
###Definições de envio
Antes de configurar o módulo do Correios em si, é necessário configurar as opções de envio da entrega. Vá em *System > Configuration > Sales > Shipping Settings*. Para que o módulo funcione corretamente é necessário apenas preencher os campos *Country e ZIP/Postal code em Origin*.

###Definições de método de entrega
Agora iremos configurar o módulo dos *Correios*. Entre *System > Configuration > Sales > Shipping Methods* e escolha a aba *Correios*. Veremos a seguir todas as opções do módulo.

+ **Habilitado** : Habilita ou desabilita o módulo.
+ **Título** : Texto que aparecerá na simulação de frete e no checkout.
+ **Código da Conta** : Não é obrigatório. É utilizado apenas por quem tem contrato com os Correios. Você receberá em seu contrato com os Correios o código da conta.
+ **Senha da Conta** : Obrigatório apenas se o *código da conta* estiver preenchido.
+ **Modo de Depuração** : Habilita o log e grava todas as mensagens enviadas pelo Webservice.
+ **Métodos de Entrega** : Selecione os métodos de entrega que estarão disponíveis para seus clientes. Observe com atenção, existem métodos de entrega que apenas com contrato irão funcionar. Se você não tem *código da conta* e *senha da conta* preenchidos, opte pelos métodos sem contrato.
+ **Utilizar valor declarado** : Utiliza a opção de valor declarado dos Correios. Para o método Sedex a cobrar esta opção é obrigatória.
+ **Método de entrega gratuíta** : Método de entrega disponível quando a entrega for gratuíta. A entrega será gratuíta quando a compra atender as regras de promoções.
+ **Adicionar ao prazo de entrega** : Adiciona dias ao prazo de entrega dos Correios.
+ **Mostrar método se não aplicável** : Esta configuração indica se as mensagens de erros devem ser apresentadas quando o método de entrega não pode ser escolhido pelo cliente.
+ **Cálculo de Taxa de Manuseio** : Tipo de cálculo feito sob a *Taxa de Manuseio*. Se a taxa de manuseio estiver em branco não será feito nenhum cálculo.
+ **Taxa de Manuseio** : A taxa de manuseio é adicionada ao valor final do preço do frete. Se deseja dar desconto use valores negativos. 
+ **Ordem** : A ordem na qual o método de entrega será mostrado ao cliente. Esta opção só é aplicavél se você tem mais de um método de entrega habilitado em sua loja virtual.

###Definições de produto
Para que o módulo funcione corretamente o peso e dimensões do produto devem ser configurados com os valores reais. O módulo considera que **todos os produtos estão com peso em KG e as dimensões dos produtos estejam em CM**.

Para configurar o produto siga os passos:

1. Vá até *Catalog > Manage Product* e edite/crie um produto. 
2. Vá na aba *General* e digite o peso do produto em KG no campo *Weight* ( peso ).
3. Vá a aba *Dimensions* ( dimensões ) e digite as dimensões do produto nos campos Altura, Largura e Comprimento. 

##Bugs
Caso encontre algum problema com o módulo não exite em [reportá-lo](https://github.com/willstorm/correios/issues).

##Dúvidas e Sugestões
Tem dúvidas, sugestões ou quer apenas dar um oi, [entre em contato com o desenvolvedor](mailto:williancordeirodesouza@gmail.com).
