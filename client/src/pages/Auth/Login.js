import React, { useState } from 'react';
import Layout from '../../components/Layout/layout';
import axios from 'axios';
import { useLocation, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { useAuth } from '../../context/auth';

const Login = () => {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const navigate = useNavigate();
    const [auth, setAuth] = useAuth();
    const location = useLocation();

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            const res = await axios.post(`${process.env.REACT_APP_API}/api/v1/auth/login`, { email, password });
            if (res && res.data.success) {
                toast.success(res.data && res.data.message);
                setAuth({
                    ...auth,
                    user: res.data.User,
                    token: res.data.token,
                });
                localStorage.setItem('auth', JSON.stringify(res.data));
                navigate(location.state || '/');
            } else {
                toast.error(res.data.message);
            }

        } catch (error) {
            console.log(error);
            toast.error('Error')
        }

    };

    return (
        <Layout title={'Login - PhoneShop'}>
            <div className="wrapper" id="container">
                <div className="form-container login-container">
                    <div className="form-login-sign">
                        <form onSubmit={handleSubmit} style={{marginTop: '10px'}}>
                            <div className="title-form">
                                <h1>Login</h1>
                            </div>
                            <div className="field">
                                <input type="email" onChange={(e) => setEmail(e.target.value)} value={email} placeholder="Email" className="input" id="email" name="email" required />
                            </div>
                            <div className="field">
                                <input type="password" onChange={(e) => setPassword(e.target.value)} value={password} placeholder="Password" className="password" id="password" name="password" required />
                            </div>
                            <div className="pass-link">
                                <a href="/forgot-password">Forgot password?</a>
                            </div>
                            <button id="btnLogin" className="btn-form">Login</button>
                        </form>
                    </div>
                    
                </div>
            </div>
        </Layout>
    )
}

export default Login