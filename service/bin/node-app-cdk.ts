#!/usr/bin/env node
import * as cdk from 'aws-cdk-lib';
import {LambdaUrlStack} from '../lib/node-app-cdk-stack';

const app = new cdk.App();
new LambdaUrlStack(app, 'NodeAppCdkStack');
