//this is hacky package.json here and node_modules and so on not proud of it
//but works for now
const { Configuration, OpenAIApi } = require("openai");


const configuration = new Configuration({
    apiKey: process.env.OPENAI_API_KEY,
});
const openai = new OpenAIApi(configuration);
exports.handler = async function(event) {
    console.log(event);

    let tokens = [];
    if ( event.body !== undefined && event.body !== null
     ) {

        let body = JSON.parse(event.body);
        console.log(body);
        if ( body.tokens !==    undefined && body.tokens !== null ) {
            tokens = body.tokens;
        }
    }
    //TODO check this and finish it
    console.log(tokens);
    if ( tokens.length >= 20 ) {
        return { statusCode : 400, body : "Too many tokens provided" };
    }
    if ( tokens.length === 0 ) {
        return { statusCode : 400, body : "No tokens provided" };
    }
    const translatePrompt = "Translate this into French, preserve formatting: ";

    const promptArray = tokens.map((token) => {
        return `${translatePrompt}${token.original}`;
    });

    console.log(promptArray);
    const response = await openai.createCompletion({
        model: "text-davinci-003",
        prompt: promptArray,
        temperature: 0.3,
        max_tokens: 100,
        top_p: 1.0,
        frequency_penalty: 0.0,
        presence_penalty: 0.0,
    });

    console.log(response.data.choices);

    return {
        statusCode: 200,
        headers: { "Content-Type": "text/json" },
        body: response.data.choices
    };
};