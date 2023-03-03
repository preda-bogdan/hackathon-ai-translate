import * as cdk from "aws-cdk-lib";
import { Construct } from "constructs";

export class LambdaUrlStack extends cdk.Stack {
  constructor(scope: Construct, id: string, props?: cdk.StackProps) {
    super(scope, id, props);

    const myLambda = new cdk.aws_lambda.Function(this, "myLambda", {
      code: new cdk.aws_lambda.AssetCode("src"),
      handler: "index.handler",
      runtime: cdk.aws_lambda.Runtime.NODEJS_16_X,
      environment: {
        "OPENAI_API_KEY": "sk-",
      }

    });

    const lambdaUrl = new cdk.aws_lambda.CfnUrl(this, "lambdaUrl", {
      targetFunctionArn: myLambda.functionArn,
      authType: cdk.aws_lambda.FunctionUrlAuthType.NONE,
      cors: {
        allowCredentials: false,
        allowHeaders: ['*'],
        allowMethods: ['*'],
        allowOrigins: ['*'],
        exposeHeaders: ['*'],
        maxAge: 0,
      },
    });
    const lambdaPermission = new cdk.CfnResource(this, "lambdaPermission", {
      type: "AWS::Lambda::Permission",
      properties: {
        Action: "lambda:InvokeFunctionUrl",
        FunctionName: myLambda.functionArn,
        Principal: "*",
        FunctionUrlAuthType: "NONE",
      },
    });
  }
}